<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatisDownloadStatistic extends Model {

    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected $primaryKey = 'package';

    protected $fillable = [
        'package', 'version', 'count',
    ];
}
