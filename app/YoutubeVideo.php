<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class YoutubeVideo extends Model
{
    protected $fillable = [
        'video_id', 'title', 'description', 'url', 'snippet', 'tags'
    ];
}
