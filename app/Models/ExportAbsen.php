<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportAbsen extends Model
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'filename',
        'path',
        'url'
    ];
}
