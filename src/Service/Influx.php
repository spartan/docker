<?php

namespace Spartan\Docker\Service;

use Spartan\Docker\Definition\ServiceAbstract;

class Influx extends ServiceAbstract
{
    const VERSION = '1.8';

    const DEFAULT_CONFIG = [
        'image'          => 'influxdb:' . self::VERSION . '-alpine',
        'container_name' => '${DOCKER_STACK}_influxdb',
        'restart'        => 'always',
        'volumes'        => [
            'influx:/var/lib/influxdb',
        ],
        'environment'    => [
            'INFLUXDB_DB=${INFLUX_DB}',
            'INFLUXDB_HTTP_AUTH_ENABLED=true',
            'INFLUXDB_ADMIN_USER=${INFLUX_ADMIN}',
            'INFLUXDB_ADMIN_PASSWORD=${INFLUX_PASS}',
            'INFLUXDB_READ_USER=${INFLUX_READ_USER}',
            'INFLUXDB_READ_PASSWORD=${INFLUX_READ_PASS}',
            'INFLUXDB_WRITE_USER=${INFLUX_WRITE_USER}',
            'INFLUXDB_WRITE_PASSWORD=${INFLUX_WRITE_PASS}',
            'INFLUXDB_DATA_MAX_VALUES_PER_TAG=0',
            'INFLUXDB_DATA_MAX_SERIES_PER_DATABASE=0',
        ],
        'ports'          => [
            '${DOCKER_PORT}86:8086',
        ],
    ];

    /**
     * @var mixed[]
     */
    protected array $externalVolumes = [
        'influx' => ['driver' => 'local'],
    ];

    /**
     * @return mixed[]
     */
    public function config(): array
    {
        $config = $this->config + self::DEFAULT_CONFIG;

        if ($this->flags & self::FLAG_IS_PRODUCTION || $this->flags & self::FLAG_EXPOSE_NONE) {
            unset($config['ports']);
        }

        return $config;
    }
}
