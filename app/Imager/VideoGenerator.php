<?php

namespace App\Imager;

use App\Jobs\UploadToServer;
use App\Jobs\UploadToYoutube;
use App\Preset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class VideoGenerator extends BaseGenerator implements GeneratorInterface
{

    public function process($data = [])
    {
        $ffmpegPath = config('imager.ffmpeg_binary_path', trim(shell_exec('which ffmpeg')));
        $ffmpegTimeout = config('imager.ffmpeg_max_timeout', (60 * 3));

        foreach ($this->presets as $preset) {
            if (!$preset->generate_video) {
                continue;
            }

            $this->preProcess($preset);

            $filename = 'video';

            # If the file exists, append the preset id to the filename
            if (Storage::exists($this->jobPath() . DIRECTORY_SEPARATOR . $filename)) {
                $filename .= $preset->id;
            }

            $filePaths = $this->getFilesPath($preset);
            $videoSourceImagesPath = $this->getVideoSourceImagesPath($preset);

            $command = $this->prepareCommand($preset, $ffmpegPath, $videoSourceImagesPath, $filename);

            $process = Process::fromShellCommandline($command, null, null, null, $ffmpegTimeout);
            $process->run();

            # executes after the command finishes
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $videoFullPath = $this->getJobFullPath() . DIRECTORY_SEPARATOR . $filename . '.mp4';

            $this->postProcess($preset, [
                'transfer' => new FtpTransfer(
                    false,
                    $this->job->folder,
                    $videoFullPath
                ),
                'video_path' => $videoFullPath,
                'meta_info' => $data['video_info']
            ]);
        }
    }

    protected function preProcess(Preset $preset, $videoData = [])
    {
        $this->job->update([
            'progress_message' => 'Preparing images for Video generation.'
        ]);

        # We need the image files to have a specific pattern for passing to the ffmpeg generator.
        $preset->filename_pattern = 'replace';
        $preset->filename = 'image-';

        # Generate the images for video creation, and store them in a temp directory.
        (new ImageGenerator($this->job, collect([$preset]), $this->files, 'video'))->process();

        $this->job->update([
            'progress_message' => 'Images creation done. Creating video.'
        ]);
    }

    protected function postProcess(Preset $preset, $videoData = [])
    {
        # Remove the video source images directory
        Storage::deleteDirectory($this->getVideoSourceImagesPath($preset));

        # Upload to ftp server
        if ($preset->video_should_upload) {
            UploadToServer::dispatch($this->section, $preset, $videoData['transfer']);
        }

        # Upload the video to youtube
        if (
            $preset->upload_to_youtube &&
            !empty($preset->videoCompany) &&
            isset($videoData['video_path']) &&
            file_exists($videoData['video_path'])
        ) {
            UploadToYoutube::dispatch($preset->videoCompany, $videoData['video_path'], $videoData['meta_info']);
        }

        $this->job->update([
            'progress_message' => 'Video has been created, writing file to disk.'
        ]);
    }

    /**
     * Prepare the command for generating the video.
     *
     * @param Preset $preset
     * @param $ffmpegPath
     * @param $videoSourceImagesPath
     * @param $filename
     * @return string
     */
    private function prepareCommand(Preset $preset, $ffmpegPath, $videoSourceImagesPath, $filename)
    {
        return implode(' ', [
            $ffmpegPath, # ffmpeg path
            '-stream_loop 1', # Set number of times input stream shall be looped. Loop 0 means no loop, loop -1 means infinite loop.
            '-y', # overwrite output without asking
            sprintf('-s %sx%s', $preset->video_width, $preset->video_height), // video resolution
            '-f image2', # Force input or output file format.
            '-framerate 1/1', # framerate format: 1/x - x seconds for each image
            '-i "' . $this->rootPath . DIRECTORY_SEPARATOR . $videoSourceImagesPath .  '/image-%03d.jpg"', # input file url or format as per c style print
            sprintf('-r %d', $preset->video_fps), # video framerate
            '-vf "pad=ceil(iw/2)*2:ceil(ih/2)*2"', # Create the filtergraph specified by filtergraph and use it to filter the stream.
            sprintf('"%s.mp4"', $this->getJobFullPath() . DIRECTORY_SEPARATOR . $filename), # Output filename
            #'2>&1 > /dev/null' # output redirection
        ]);
    }

    /**
     * Get all the file paths.
     *
     * @param Preset $preset
     * @return array
     */
    private function getFilesPath(Preset $preset)
    {
        $sourceImagesPath = $this->getVideoSourceImagesPath($preset);

        $files = Storage::files($sourceImagesPath);
        $filePaths = [];

        foreach ($files as $file) {
            $filePaths[] = $this->rootPath . DIRECTORY_SEPARATOR . $file;
        }

        return $filePaths;
    }

    private function getVideoSourceImagesPath(Preset $preset)
    {
        $folder = static::getSectionTempDirectory($preset->id);

        return $this->jobPath($folder);
    }

    public static function getSectionTempDirectory($id = '')
    {
        return 'video_images' . $id;
    }

}
