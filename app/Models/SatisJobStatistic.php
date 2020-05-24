<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatisJobStatistic extends Model {

    public $incrementing = false;

    protected $primaryKey = 'uuid';

    protected $fillable = [
        'uuid', 'job', 'avg_runtime', 'count',
    ];
}
