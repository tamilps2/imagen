<?php

namespace App\Jobs;

use App\Company;
use App\Imager\FtpTransfer;
use App\Preset;
use FtpClient\FtpClient;
use FtpClient\FtpException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;
use Illuminate\Support\Str;

class UploadToServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $preset;

    public $ftpTransfer;

    public $section;

    public function __construct($section, Preset $preset, FtpTransfer $ftpTransfer)
    {
        $this->section = $section;
        $this->preset = $preset;
        $this->ftpTransfer = $ftpTransfer;

        if (!is_dir($this->ftpTransfer->uploadPath)) {
            $this->fail(new Exception('Path provided is not a directory'));
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws FtpException
     */
    public function handle()
    {
        $company = $this->getCompanyForPresetSection();

        if (empty($company)) {
            $this->fail(new Exception('Company does not exists for preset [' . $this->preset->id . ']'));
            return;
        }

        $ftp = new FtpClient();

        try {
            list($host, $port) = $this->getHostDetails($company->ftp_host);

            $ftp->connect($host, false, $port);
            $ftp->login($company->ftp_username, $company->ftp_password);

            $targetDirectory = $this->getTargetDirectory(
                $company->ftp_username,
                $company->ftp_upload_path,
                $this->ftpTransfer->folderPath
            );

            # Prepare all directories
            $ftp->mkdir($targetDirectory, true);

            # Check if transfer is a file or directory and upload accordingly
            if ($this->ftpTransfer->isDirectory) {
                $ftp->putAll($this->ftpTransfer->uploadPath, $targetDirectory);
            } else {
                $remoteFile = $targetDirectory . '/' . pathinfo($this->ftpTransfer->uploadPath, PATHINFO_BASENAME);
                $ftp->put($remoteFile, $this->ftpTransfer->uploadPath, FTP_BINARY);
            }
        } catch (FtpException $e) {
            $this->fail(new Exception('Failed to transfer files. ' . $e->getMessage()));
        }
    }

    /**
     * Get the correct host and port details
     *
     * @param $host
     * @return array
     */
    protected function getHostDetails($host)
    {
        $port = 21;

        if (Str::contains($host, ':')) {
            list($host, $port) = explode(':', $host);
        }

        return [$host, $port];
    }

    /**
     * Get the remote server target directory
     *
     * @param $user
     * @param $path
     * @param string $append
     * @return string
     */
    protected function getTargetDirectory($user, $path, $append = '')
    {
        $uploadPath = '';

        if (!empty($path)) {
            $uploadPath = ltrim($path, '/');
        }

        $uploadPath = ltrim(rtrim($uploadPath, '/') . DIRECTORY_SEPARATOR . $append, '/');

        return $uploadPath;
    }

    /**
     * Get the company for the preset section
     *
     * @return mixed|null
     */
    protected function getCompanyForPresetSection()
    {
        if ($this->section === 'sm') {
            return $this->preset->smallImageCompany;
        } else if ($this->section === 'lg') {
            return $this->preset->largeImageCompany;
        } else if ($this->section === 'gif') {
            return $this->preset->gifCompany;
        } else if ($this->section === 'video') {
            return $this->preset->videoCompany;
        } else {
            return null;
        }
    }
}
