<?php

namespace Tests\Feature;

use App\File;
use App\Job;
use App\Preset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait GenerationTestHelpers
{
    private function createJobFile($job, $filename, $width = 1920, $height = 1080)
    {
        $file = UploadedFile::fake()->image($filename, $width, $height);

        Storage::putFileAs(
            $this->uploadsDirectory . DIRECTORY_SEPARATOR . $job->folder,
            $file,
            $file->getClientOriginalName()
        );

        return factory(File::class)->create([
            'job_id' => $job->id,
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'extension' => $file->getClientOriginalExtension(),
            'folder' => $job->folder,
            'original_name' => $file->name,
            'storage_path' => $this->uploadsDirectory . DIRECTORY_SEPARATOR . $job->folder,
            'full_path' => storage_path('app/' . $this->uploadsDirectory . DIRECTORY_SEPARATOR . $job->folder)
        ]);
    }

    private function createJob($folder)
    {
        Storage::deleteDirectory($this->uploadsDirectory . DIRECTORY_SEPARATOR . $folder);

        $job = factory(Job::class)->create([
            'title' => $folder,
            'folder' => $folder,
            'is_processed' => 0,
            'is_processing' => 0
        ]);

        return $job;
    }

    private function createImagePreset($section)
    {
        $presetValues = [
            'filename_pattern' => 'prepend',
            'filename' => 'test',
            $section . '_width' => ($section === 'lg' ? 1920 : 750),
            $section . '_height' => ($section === 'lg' ? 1080: 700),
            $section . '_watermark' => true,
            $section . '_company_id' => 1,
            $section . '_wm_position' => 'bottom-right',
            $section . '_wm_unit' => 'px',
            $section . '_wm_x_axis' => 3,
            $section . '_wm_y_axis' => 3,
        ];

        if ($section === 'sm') {
            $presetValues['generate_small_image'] = true;
            $presetValues['generate_large_image'] = false;
        } else if ($section === 'lg') {
            $presetValues['generate_small_image'] = false;
            $presetValues['generate_large_image'] = true;
        }

        $presetValues['generate_gif'] = false;
        $presetValues['generate_video'] = false;

        return factory(Preset::class)->create($presetValues);
    }

    private function createGifPreset($interval = 1)
    {
        $presetValues = [
            'filename_pattern' => 'prepend',
            'filename' => 'test',
            'generate_gif' => true,
            'gif_interval' => $interval,
            'gif_width' => 750,
            'gif_height' => 700,
            'gif_watermark' => true,
            'gif_company_id' => 1,
            'gif_wm_position' => 'bottom-right',
            'gif_wm_unit' => 'px',
            'gif_wm_x_axis' => 3,
            'gif_wm_y_axis' => 3,
            'generate_small_image' => false,
            'generate_large_image' => false,
            'generate_video' => false,
        ];

        return factory(Preset::class)->create($presetValues);
    }

    private function createVideoPreset($fps = 1)
    {
        $presetValues = [
            'filename_pattern' => 'prepend',
            'filename' => 'test',
            'generate_video' => true,
            'video_fps' => $fps,
            'video_width' => 1920,
            'video_height' => 1080,
            'video_watermark' => true,
            'video_company_id' => 1,
            'video_wm_position' => 'bottom-right',
            'video_wm_unit' => 'px',
            'video_wm_x_axis' => 3,
            'video_wm_y_axis' => 3,
            'generate_small_image' => false,
            'generate_large_image' => false,
            'generate_gif' => false,
            'upload_to_youtube' => true
        ];

        return factory(Preset::class)->create($presetValues);
    }
}