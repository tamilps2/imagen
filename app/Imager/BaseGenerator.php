<?php

namespace App\Imager;

use App\Job;
use App\Preset;
use Illuminate\Support\Collection;

abstract class BaseGenerator
{
    /**
     * The job to process
     *
     * @var Job
     */
    protected $job;

    /**
     * Presets to process the job with
     *
     * @var array|Collection
     */
    protected $presets;

    /**
     * Job files
     *
     * @var array|Collection
     */
    public $files = [];

    /**
     * Image size to generate
     *
     * sm|lg|gif|video
     *
     * @var string
     */
    protected $section;

    /**
     * Job output folder path relative to storage path
     *
     * @var string
     */
    protected $outputPath;

    /**
     * Storage full path
     *
     * @var string
     */
    protected $rootPath;

    /**
     * ImageGenerator constructor.
     *
     * @param Job $job
     * @param Collection $presets
     * @param Collection $files
     * @param string $section
     * @throws GeneratorException
     */
    public function __construct(Job $job, Collection $presets, Collection $files, $section = 'sm')
    {
        $this->job = $job;
        $this->presets = $presets;
        $this->files = $files;
        $this->section = $section;
        $this->outputPath = config('imager.job_output_dir', 'jobs/output');
        $this->rootPath = config('filesystems.disks.local.root', 'app');

        if (!in_array($section, ['sm', 'lg', 'gif', 'video'])) {
            throw new GeneratorException("Unsupported preset section[{$section}] passed.");
        }
    }

    /**
     * Prepare stuffs before the process
     *
     * @param Preset $preset
     * @param $data array
     * @return mixed
     * @throws GeneratorException
     */
    abstract protected function preProcess(Preset $preset, $data = []);

    /**
     * Do stuffs after the process is complete
     *
     * @param Preset $preset
     * @param $data array
     * @return mixed
     * @throws GeneratorException
     */
    abstract protected function postProcess(Preset $preset, $data = []);

    /**
     * Get full path for the job
     *
     * @param string $path
     * @return string
     */
    protected function getJobFullPath($path = '')
    {
        return ($this->rootPath . DIRECTORY_SEPARATOR . $this->jobPath($path));
    }

    /**
     * Get the relative job path to the storage output
     *
     * @param string $path
     * @return string
     */
    protected function jobPath($path = '')
    {
        $jobPath = implode(DIRECTORY_SEPARATOR, [
            $this->outputPath,
            $this->job->folder,
            $path
        ]);

        $jobPath = trim($jobPath, DIRECTORY_SEPARATOR);

        return $jobPath;
    }

    /**
     * Calculate the percent for the currently processed images.
     *
     * @param $total
     * @param $index
     * @return float|int|mixed
     */
    protected function calculatePercentage($total, $index)
    {
        $currentlyProcessed = ($index + 1);
        $sectionPercent = (($currentlyProcessed * 100) / $total);

        return round($sectionPercent);
    }
}