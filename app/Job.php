<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $fillable = [
        'title', 'folder', 'is_processed', 'is_processing', 'has_errors', 'processed_at',
        'progress', 'progress_message', 'section_progress'
    ];

    protected $casts = [
        'is_processed' => 'boolean',
        'is_processing' => 'boolean',
        'has_errors' => 'boolean',
        'processed_at' => 'timestamp',
        'progress' => 'integer',
        'section_progress' => 'integer'
    ];

    public function getUploadPath()
    {
        $upload_directory = config('imager.job_upload_dir', 'jobs/uploads');

        return $upload_directory . DIRECTORY_SEPARATOR . $this->folder;
    }

    public function getOutputPath()
    {
        $output_directory = config('imager.job_output_dir', 'jobs/output');

        return $output_directory . DIRECTORY_SEPARATOR . $this->folder;
    }

    public function presets()
    {
        return $this->belongsToMany(Preset::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }
}
