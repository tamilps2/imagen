<?php


namespace App\Imager;

/**
 * Class FtpTransfer
 *
 * @package App\Imager
 */
class FtpTransfer
{
    /**
     * Specifies if the upload is a directory
     *
     * @var string
     */
    public $isDirectory;

    /**
     * The folder path to maintain in remote server
     *
     * @var string
     */
    public $folderPath;

    /**
     * Absolute path to the upload directory/file
     *
     * @var string
     */
    public $uploadPath;

    public function __construct($isDirectory, $folderPath, $uploadPath)
    {
        $this->isDirectory = $isDirectory;
        $this->folderPath = $folderPath;
        $this->uploadPath = $uploadPath;
    }
}