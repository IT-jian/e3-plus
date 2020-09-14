<?php

namespace App\Models;

use App\Models\BaseModel as Model;

class Shangdian extends Model
{
    protected $table = 'shangdian';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'name', 'platform_code', 'api_params',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $casts = [
        'api_params' => 'array',
    ];
}
