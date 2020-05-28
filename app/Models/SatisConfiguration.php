<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatisConfiguration extends Model {

    public $appends = [
        'password',
    ];

    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected $primaryKey = 'uuid';

    protected $fillable = [
        'uuid',
        'name',
        'homepage',
        'password_secured',
        'configuration',
        'crontab',
    ];

    protected $hidden = [
        'configuration',
        'password',
        'created_at',
        'updated_at',
    ];


    /**
     * @return string
     */
    public function getConfigurationAttribute() {
        return $this->prepareConfig();
    }


    public function getHomepageAttribute() {
        return rtrim(array_get($this->attributes, 'homepage', ''), '/');
    }


    /**
     * @return array
     */
    public function getRepositoriesAttribute(): array {
        return array_get(
            json_decode(array_get($this->attributes, 'configuration', '{}'), true),
            'repositories',
            []
        );
    }


    /**
     * @return string
     */
    public function getOutputDirAttribute(): string {
        return array_get(json_decode($this->getConfigurationAttribute(), true), 'output-dir', '');
    }


    public function getPasswordAttribute() {
        return config('satis.htpasswd_password');
    }


    public function setCrontabAttribute($value) {
        $this->attributes['crontab'] = trim(preg_replace('/\s+/', ' ', $value));
    }


    /**
     * @return string
     */
    private function prepareConfig(): string {
        $data = json_decode(array_get($this->attributes, 'configuration', '{}'), true);

        if (!$this->homepage && $this->exists) {
            $homepage = self::where('uuid', $this->uuid)->value('homepage');

            if (!$homepage) {
                throw new \RuntimeException('Can not load configuration');
            }

            $this->homepage = $homepage;
        } elseif (!$this->homepage && !$this->exists) {
            $this->homepage = array_get($data, 'homepage');
        }

        // always set homepage
        $data['homepage'] = $this->homepage;

        // always set output dir
        $data['output-dir'] = sprintf('%s/%s', config('satis.output_dir'), $this->uuid);

        // specific archive settings
        $data['archive']['directory'] = 'dist';
        unset($data['archive']['absolute-directory']);

        // write back the attribute
        $this->attributes['configuration'] = json_encode($data);

        return $this->attributes['configuration'];
    }
}
