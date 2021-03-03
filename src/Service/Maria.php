<?php

namespace Spartan\Docker\Service;

use Spartan\Docker\Definition\ServiceAbstract;

class Maria extends ServiceAbstract
{
    const VERSION = '10.5';

    const DEFAULT_CONFIG = [
        'image'          => 'mariadb:' . self::VERSION,
        'container_name' => '${DOCKER_STACK}_maria',
        'restart'        => 'always',
        'ports'          => [
            '${DOCKER_PORT}33:3306',
        ],
        'volumes'        => [
            'maria:/var/lib/mysql',
            '.:/etc/mysql/conf.d',
        ],
        'environment'    => [
            'MYSQL_ROOT_PASSWORD=${DB_ROOT}',
            'MYSQL_DATABASE=${DB_NAME}',
            'MYSQL_USER=${DB_USER}',
            'MYSQL_PASSWORD=${DB_PASS}',
        ],
    ];

    /**
     * @var mixed[]
     */
    protected array $externalVolumes = [
        'mysql' => ['driver' => 'local'],
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
