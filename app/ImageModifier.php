<?php

namespace App;

use App\Job;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageModifier
{
    /**
     * @var Job
     */
    public $job;

    /**
     * Files to process
     *
     * @var array|Collection
     */
    public $files = [];

    /**
     * File processing preset
     *
     * @var Preset
     */
    public $preset;

    /**
     * Preset options
     *
     * @var mixed
     */
    public $options;

    public function __construct(Job $job)
    {
        $this->job = $job;
        $this->files = $job->files;
        $this->preset = $job->preset;
        $this->options = json_decode($this->preset->options);
    }

    public function process()
    {
        $job_directory = $this->job->getJobDirectory();
        $output_directory = $this->job->getOutputDirectory();

        if (empty($this->options->resolutions)) {
            return [
                'status' => false,
                'message' => 'No resolutions specified for output.'
            ];
        }

        # If the output directory exists, delete it.
        if (Storage::exists($output_directory)) {
            Storage::deleteDirectory($output_directory);
        }

        Storage::makeDirectory($output_directory);

        # make directories for different resolutions
        $resolutionDirectories = [];
        foreach ($this->options->resolutions as $resolution) {
            if (!empty($resolution->width) && !empty($resolution->height)) {
                $resolutionDirectory = (int)$resolution->width . 'x' . (int)$resolution->height;
                $resOutputDirectory = $output_directory . DIRECTORY_SEPARATOR . $resolutionDirectory;
                if (Storage::makeDirectory($resOutputDirectory)) {
                    $resolutionDirectories[] = $resOutputDirectory;
                }
            }
        }

        // process each image for different resolutions
        $this->files->each(function ($file, $index) use ($output_directory) {
            foreach ($this->options->resolutions as $resolution) {
                if (!empty($resolution->width) && !empty($resolution->height)) {
                    $resolutionDirectory = (int)$resolution->width . 'x' . (int)$resolution->height;
                    $resOutputDirectory = $output_directory . DIRECTORY_SEPARATOR . $resolutionDirectory;

                    echo storage_path($file->full_path);
                    $image = Image::make(storage_path($file->full_path))
                        ->resize($resolution->width, $resolution->height);

                    // later add watermark.
                    $filename = $file->name;
                    if ($this->options->filename_prefix) {
                        $filename = sprintf("%s%04d", $this->options->filename_prefix, $index);
                    }

                    $image->save(storage_path($resOutputDirectory . DIRECTORY_SEPARATOR . $filename));
                }
            }
        });
        // store the images in their respective folders
    }

}
