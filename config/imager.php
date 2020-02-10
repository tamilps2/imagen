<?php

return [

    'default_job_directory_prefix' => 'job',

    /**
     * Upload directory path with reference to the storage folder.
     *
     * @var string
     */
    'job_upload_dir' => 'jobs/uploads',

    /**
     * Output directory path with reference to the storage folder.
     *
     * @var string
     */
    'job_output_dir' => 'jobs/output',

    /**
     * Path relative to storage disk.
     */
    'logos_dir' => 'logos',

    /**
     * Global watermark settings
     */
    'watermark' => [
        'width' => 100,
        'height' => 100,
        'opacity' => 50
    ],

    /**
     * ffmpeg binary full path.
     */
    'ffmpeg_binary_path' => '/usr/bin/ffmpeg',

    /**
     * ffmpeg video generation timeout in seconds
     *
     * set to 5 mins for now.
     */
    'ffmpeg_max_timeout' => (60 * 5),

    /**
     * Enable file section progress tracking.
     *
     * This will take additional milliseconds of processing.
     */
    'track_section_progress' => true
];
