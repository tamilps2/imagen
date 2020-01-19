<?php

namespace App\Imager;

use App\Job;
use App\Jobs\UploadToServer;
use App\Preset;
use GifCreator\GifCreator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GifGenerator extends BaseGenerator implements GeneratorInterface
{

    public function process($data = [])
    {
        foreach ($this->presets as $preset) {
            if (!$preset->generate_gif) {
                continue;
            }

            $filename = 'gif';

            $this->preProcess($preset);

            $filePaths = $this->getFilesPath($preset);

            $creator = new GifCreator();
            $creator->create(
                $filePaths,
                array_fill(0, count($filePaths), (60 * $preset->gif_interval))
            );

            # If the file exists, append the preset id to the filename
            if (Storage::exists($this->jobPath($filename))) {
                $filename .= $preset->id;
            }

            $gifFullPath = $this->getJobFullPath() . DIRECTORY_SEPARATOR . $filename . '.gif';

            file_put_contents(
                $gifFullPath,
                $creator->getGif()
            );

            $this->postProcess($preset, [
                'transfer' => new FtpTransfer(
                    false,
                    $this->job->folder,
                    $gifFullPath
                )
            ]);
        }
    }

    protected function preProcess(Preset $preset, $gifData = [])
    {
        $this->job->update([
            'progress_message' => 'Preparing images for GIF generation.'
        ]);

        # Prepare the images for gif creation and store them in a temp folder
        (new ImageGenerator($this->job, collect([$preset]), $this->files, 'gif'))->process();

        $this->job->update([
            'progress_message' => 'Finished preparing images for GIF.'
        ]);
    }

    protected function postProcess(Preset $preset, $gifData = [])
    {
        # Remove the gif source images directory
        Storage::deleteDirectory($this->getGifSourceImagesPath($preset));

        # Upload file to server.
        if ($preset->gif_should_upload) {
            UploadToServer::dispatch($this->section, $preset, $gifData['transfer']);
        }

        $this->job->update([
            'progress_message' => 'Gif has been generated.'
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
        $gifSourceImagesPath = $this->getGifSourceImagesPath($preset);

        $files = Storage::files($gifSourceImagesPath);
        $filePaths = [];

        foreach ($files as $file) {
            $filePaths[] = $this->rootPath . DIRECTORY_SEPARATOR . $file;
        }

        return $filePaths;
    }

    private function getGifSourceImagesPath(Preset $preset)
    {
        $folder = static::getSectionTempDirectory($preset->id);

        return $this->jobPath($folder);
    }

    public static function getSectionTempDirectory($id = '')
    {
        return 'gif_images' . $id;
    }
}
