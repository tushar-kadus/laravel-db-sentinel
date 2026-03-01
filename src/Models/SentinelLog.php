<?php

namespace Atmos\DbSentinel\Models;

use Illuminate\Database\Eloquent\Model;

class SentinelLog extends Model
{
    protected $fillable = [
        'connection',
        'hash',
        'sql',
        'bindings',
        'execution_time',
        'caller',
        'url',
        'method',
        'explanation',
        'suggestions',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'bindings' => 'array',
        'execution_time'  => 'float',
        'caller'   => 'array',
        'explanation' => 'array',
        'suggestions' => 'array',
    ];

    /**
     * Set the table name from the config.
     */
    public function getTable()
    {
        return config('db-sentinel.database.table_name');
    }

    /**
     * Set the connection from the config.
     */
    public function getConnectionName()
    {
        return config('db-sentinel.database.connection', config('database.default'));
    }

    /**
     * Get Severity count from suggestions
     *
     * @return array
     */
    public function getSeverityCountsAttribute(): array
    {
        if (empty($this->suggestions)) {
             return [];
        }

        return collect($this->suggestions)->countBy('severity')->toArray();
    }
}
