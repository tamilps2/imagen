<?php

namespace App\Imager;

use App\Jobs\UploadToServer;
use App\Preset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use PHPUnit\Framework\Constraint\DirectoryExists;

class ImageGenerator extends BaseGenerator implements GeneratorInterface
{
    /**
     * Start the generation process
     *
     * @param $data
     * @throws GeneratorException
     */
    public function process($data = [])
    {
        foreach ($this->presets as $preset) {
            $this->preProcess($preset, []);

            if (!$this->checkShouldGenerateForSection($preset)) {
                continue;
            }

            list($sectionDirectory, $sectionStorageDirectoryPath) = $this->checkPresetSectionDirectory($preset);

            $outputFiles = $this->generateImages($preset, $sectionDirectory, $sectionStorageDirectoryPath);

            $this->postProcess($preset, [
                'transfer' => new FtpTransfer(
                    true,
                    $this->job->folder . DIRECTORY_SEPARATOR . $sectionDirectory,
                    $this->getJobFullPath($sectionDirectory)
                )
            ]);
        }
    }

    /**
     * Generate images
     *
     * @param Preset $preset
     * @param $sectionDirectory
     * @param $sectionStorageDirectoryPath
     * @return array
     * @throws GeneratorException
     */
    private function generateImages(Preset $preset, $sectionDirectory, $sectionStorageDirectoryPath)
    {
        $outputFiles = [];
        $sectionProgress = config('imager.track_section_progress', false);
        $totalFilesCount = count($this->files);

        foreach ($this->files as $index => $file) {
            $filename = $this->generatePresetFilename($preset, $file, $index);

            $filePath = implode(DIRECTORY_SEPARATOR, [
                $this->rootPath, $sectionStorageDirectoryPath, $filename
            ]);

            # Load the image
            $image = Image::make($file->getFile())
                ->fit($preset->{$this->section . '_width'}, $preset->{$this->section . '_height'});

            # Check and apply watermark
            if ($preset->{$this->section . '_watermark'}) {
                $image = $this->applyWatermark($image, $preset);
            }

            $image->save($filePath);

            $jobSectionDirectory = $this->job->folder . DIRECTORY_SEPARATOR . $sectionDirectory;
            $outputFiles[$jobSectionDirectory][] = $filePath;

            if ($sectionProgress) {
                $this->job->update([
                    'section_progress' => $this->calculatePercentage($totalFilesCount, $index)
                ]);
            }
        }

        return $outputFiles;
    }

    protected function preProcess(Preset $preset, $data = [])
    {
        $this->job->update([
            'progress' => $this->preProcessPercent(),
            'section_progress' => 0,
            'progress_message' => sprintf(
                'Checking and generating %s images for job[%s] with preset[%s].',
                $this->getSectionString(),
                $this->job->folder,
                $preset->name
            )
        ]);
    }

    protected function postProcess(Preset $preset, $data = [])
    {
        # Only check upload to server for small and large image
        if (in_array($this->section, ['sm', 'lg']) && $preset->{$this->section . '_should_upload'}) {
            UploadToServer::dispatch($this->section, $preset, $data['transfer']);
        }

        $this->job->update([
            'progress_message' => sprintf(
                'Finished generating %s images for job[%s] with preset[%s].',
                $this->getSectionString(),
                $this->job->folder,
                $preset->name
            )
        ]);
    }

    /**
     * Check if generator should be run for this preset.
     *
     * @param Preset $preset
     * @return bool
     */
    protected function checkShouldGenerateForSection(Preset $preset)
    {
        if ($this->section === 'sm' && $preset->generate_small_image) {
            return true;
        } else if ($this->section === 'lg' && $preset->generate_large_image) {
            return true;
        } else if ($this->section === 'gif' && $preset->generate_gif) {
            return true;
        } else if ($this->section === 'video' && $preset->generate_video) {
            return true;
        }

        return false;
    }

    /**
     * Get the filename based on the preset setting.
     *
     * @param $preset
     * @param $file
     * @param $index
     * @return string
     * @throws GeneratorException
     */
    protected function generatePresetFilename($preset, $file, $index)
    {
        if ($preset->filename_pattern === 'original') {
            return sprintf('%s.%s', $file->name, $file->extension);
        } else if ($preset->filename_pattern === 'replace') {
            return sprintf('%s%03d.%s', $preset->filename, ($index + 1), $file->extension);
        } else if ($preset->filename_pattern === 'prepend') {
            return sprintf('%s%s.%s', $preset->filename, $file->name, $file->extension);
        } else if ($preset->filename_pattern === 'append') {
            return sprintf('%s%s.%s', $file->name, $preset->filename, $file->extension);
        }

        throw new GeneratorException('Invalid preset filename pattern.');
    }

    /**
     * Apply the watermark for the image.
     *
     * @param $image
     * @param Preset $preset
     * @return mixed
     * @throws GeneratorException
     */
    protected function applyWatermark($image, Preset $preset)
    {
        $watermarkWidth = config('imager.watermark.width', 100);
        $watermarkHeight = config('imager.watermark.height', 100);
        $watermarkOpacity = config('imager.watermark.opacity', 100);

        $company = $this->getCompanyForSection($preset);

        $watermark = Image::make($company->getCompanyLogo())
            ->resize($watermarkWidth, null, function ($constraint) {
                $constraint->aspectRatio();
            })->opacity($watermarkOpacity);

        if ($preset->{$this->section . '_wm_unit'} === 'auto') {
            $image->insert(
                $watermark,
                $preset->{$this->section . '_wm_position'}
            );
        } else if ($preset->{$this->section . '_wm_unit'} === 'percent') {
            $xAxis = round((((int)$preset->{$this->section . '_width'} * (int)$preset->{$this->section . '_wm_x_axis'}) / 100));
            $yAxis = round((((int)$preset->{$this->section . '_height'} * (int)$preset->{$this->section . '_wm_y_axis'}) / 100));

            $image->insert(
                $watermark,
                $preset->{$this->section . '_wm_position'},
                $xAxis,
                $yAxis
            );
        } else {
            $image->insert(
                $watermark,
                $preset->{$this->section . '_wm_position'},
                $preset->{$this->section . '_wm_x_axis'},
                $preset->{$this->section . '_wm_y_axis'}
            );
        }

        return $image;
    }

    /**
     * Get the correct company for the section.
     *
     * @param Preset $preset
     * @return mixed
     * @throws GeneratorException
     */
    protected function getCompanyForSection(Preset $preset)
    {
        if ($this->section === 'sm') {
            return $preset->smallImageCompany;
        } else if ($this->section === 'lg') {
            return $preset->largeImageCompany;
        } else if ($this->section === 'gif') {
            return $preset->gifCompany;
        } else if ($this->section === 'video') {
            return $preset->videoCompany;
        }

        throw new GeneratorException("Unsupported preset section[$this->section].");
    }

    /**
     * Make sure the directory exists for putting the preset resolution
     *
     * @param Preset $preset
     * @return array
     */
    protected function checkPresetSectionDirectory(Preset $preset)
    {
        $sectionDirectory = '';

        if (in_array($this->section, ['sm', 'lg'])) {
            $sectionDirectory = sprintf('%sx%s', $preset->{$this->section . '_width'}, $preset->{$this->section . '_height'});
        } else if ($this->section === 'gif') {
            $sectionDirectory = GifGenerator::getSectionTempDirectory($preset->id);
        } else if ($this->section === 'video') {
            $sectionDirectory = VideoGenerator::getSectionTempDirectory($preset->id);
        }

        $sectionStorageDirectoryPath = $this->jobPath($sectionDirectory);

        if (Storage::exists($sectionStorageDirectoryPath)) {
            Storage::deleteDirectory($sectionStorageDirectoryPath);
        }

        Storage::makeDirectory($sectionStorageDirectoryPath);

        return [$sectionDirectory, $sectionStorageDirectoryPath];
    }

    protected function getSectionString()
    {
        $dictionary = [
            'sm' => 'Small',
            'lg' => 'Large',
            'gif' => 'GIF',
            'video' => 'Video'
        ];

        return $dictionary[$this->section];
    }

    protected function preProcessPercent()
    {
        $presetCount = count($this->presets);

        $percentages = [
            'sm' => 0,
            'lg' => round(25 / $presetCount),
            'gif' => round(50/ $presetCount),
            'video' => round(75 / $presetCount)
        ];

        return $percentages[$this->section];
    }

    protected function postProcessPercent()
    {
        $presetCount = count($this->presets);

        $percentages = [
            'sm' => 25,
            'lg' => 50,
            'gif' => 70,
            'video' => round(90 / $presetCount)
        ];

        return $percentages[$this->section];
    }

}
