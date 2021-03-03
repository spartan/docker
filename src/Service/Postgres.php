<?php

namespace Spartan\Docker\Service;

use Spartan\Docker\Definition\ServiceAbstract;

class Postgres extends ServiceAbstract
{
    const VERSION = '13.1';

    const DEFAULT_CONFIG = [
        'image'          => 'postgres:' . self::VERSION,
        'container_name' => '${DOCKER_STACK}_postgres',
        'restart'        => 'always',
        'ports'          => [
            '${DOCKER_PORT}32:5432',
        ],
        'volumes'        => [
            'postgres:/var/lib/postgresql/data',
        ],
        'environment'    => [
            'POSTGRES_DB=${DB_NAME}',
            'POSTGRES_USER=${DB_USER}',
            'POSTGRES_PASSWORD=${DB_PASS}',
        ],
    ];

    /**
     * @var mixed[]
     */
    protected array $externalVolumes = [
        'postgres' => ['driver' => 'local'],
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
