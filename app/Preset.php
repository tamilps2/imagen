<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Preset extends Model
{
    protected $fillable = [
        'name', 'filename_pattern', 'filename', 'generate_small_image', 'sm_width', 'sm_height',
        'sm_watermark', 'sm_company_id', 'sm_wm_position', 'sm_wm_unit', 'sm_wm_x_axis', 'sm_wm_y_axis',
        'generate_large_image', 'lg_width', 'lg_height', 'lg_watermark', 'lg_company_id', 'lg_wm_position',
        'lg_wm_unit', 'lg_wm_x_axis', 'lg_wm_y_axis', 'generate_gif', 'gif_width', 'gif_height',
        'gif_watermark', 'gif_company_id', 'gif_wm_position', 'gif_wm_unit', 'gif_wm_x_axis', 'gif_wm_y_axis',
        'gif_interval', 'generate_video', 'video_width', 'video_height', 'video_watermark',
        'video_company_id', 'video_wm_position', 'video_wm_unit', 'video_wm_x_axis', 'video_wm_y_axis',
        'video_fps', 'sm_should_upload', 'lg_should_upload', 'gif_should_upload', 'video_should_upload',
        'upload_to_youtube'
    ];

    protected $casts = [
        'generate_small_image' => 'boolean',
        'sm_watermark' => 'boolean',
        'generate_large_image' => 'boolean',
        'lg_watermark' => 'boolean',
        'generate_gif' => 'boolean',
        'gif_watermark' => 'boolean',
        'generate_video' => 'boolean',
        'video_watermark' => 'boolean',
        'sm_should_upload' => 'boolean',
        'lg_should_upload' => 'boolean',
        'gif_should_upload' => 'boolean',
        'video_should_upload' => 'boolean',
        'upload_to_youtube' => 'boolean'
    ];

    public static function availablePositions()
    {
        return [
            'top-left',
            'top',
            'top-right',
            'left',
            'center',
            'right',
            'bottom-left',
            'bottom',
            'bottom-right'
        ];
    }

    public static function availableUnits()
    {
        return $units = [
            'auto',
            'px',
            'percent'
        ];
    }

    public function presetFilename($filename)
    {

    }

    public function getActiveSections()
    {
        $active = 0;
        $sections = [
            'generate_small_image',
            'generate_large_image',
            'generate_gif',
            'generate_video'
        ];

        foreach ($sections as $section) {
            if ($this->$section) {
                $active += 1;
            }
        }

        return $active;
    }

    public function getSectionDirectoryName($section = 'sm')
    {
        return sprintf('%sx%s', $this->{$section . '_width'}, $this->{$section . '_height'});
    }

    public function jobs()
    {
        return $this->belongsToMany(Job::class);
    }

    public function smallImageCompany()
    {
        return $this->hasOne(Company::class, 'id', 'sm_company_id');
    }

    public function largeImageCompany()
    {
        return $this->hasOne(Company::class, 'id', 'lg_company_id');
    }

    public function gifCompany()
    {
        return $this->hasOne(Company::class, 'id', 'gif_company_id');
    }

    public function videoCompany()
    {
        return $this->hasOne(Company::class, 'id', 'video_company_id');
    }
}
