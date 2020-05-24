<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatisJobStatistic extends Model {

    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected $primaryKey = 'uuid';

    protected $fillable = [
        'uuid', 'job', 'avg_runtime', 'count',
    ];
}
