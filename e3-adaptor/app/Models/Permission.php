<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as BasePermission;

/**
 * Class Permission
 *
 * @package App\Models
 * @version October 28, 2018, 3:59 pm CST
 * @property integer id
 * @property string name
 * @property string desc
 * @property integer parent_id
 * @property string guard_name
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\Spatie\Permission\Models\Permission permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|\Spatie\Permission\Models\Permission role($roles)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Permission query()
 */
class Permission extends BasePermission
{

    public $table = 'permissions';

    public $fillable = [
        'id',
        'name',
        'desc',
        'guard_name',
        'parent_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'desc' => 'string',
        'guard_name' => 'string',
        'parent_id' => 'integer'
    ];

    public function getCachePermissions()
    {
        return $this->getPermissions();
    }
}