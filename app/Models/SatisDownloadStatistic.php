<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatisDownloadStatistic extends Model {

    public $incrementing = false;

    protected $primaryKey = 'package';

    protected $fillable = [
        'package', 'version', 'count',
    ];
}
