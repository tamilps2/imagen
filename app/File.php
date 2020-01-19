<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    protected $fillable = [
        'job_id',
        'name', // filename w/o extension
        'extension', // file extension
        'folder', // file folder name
        'original_name', // original name with extension
        'storage_path',  // full relative path to storage w file folder
        'full_path', // absolute path
    ];

    public function getStoragePath()
    {
        return ($this->storage_path . DIRECTORY_SEPARATOR . $this->original_name);
    }

    public function getFile()
    {
        return Storage::get($this->storage_path . DIRECTORY_SEPARATOR . $this->original_name);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
