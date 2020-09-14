<?php


namespace App\Models;


trait PlatformTrait
{

    public function scopePlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }
}