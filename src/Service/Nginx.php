<?php

namespace Spartan\Docker\Service;

use Spartan\Docker\Definition\ServiceAbstract;

class Nginx extends ServiceAbstract
{
    const VERSION = '1.19';

    const DEFAULT_CONFIG = [
        'image'          => 'nginx:' . self::VERSION . '-alpine',
        'container_name' => '${DOCKER_STACK}_nginx',
        'restart'        => 'always',
        'working_dir'    => '/var/www',
        'depends_on'     => [],
        'environment'    => [
            'VIRTUAL_HOST=${APP_DOMAIN}',
            'LETSENCRYPT_HOST=${APP_DOMAIN}',
            'LETSENCRYPT_EMAIL=${APP_EMAIL}',
        ],
        'volumes'        => [
            self::PATH_TO_ROOT . ':/var/www',
            './nginx.conf:/etc/nginx/conf.d/default.conf',
        ],
        'ports'          => [
            '${DOCKER_PORT}80:80',
        ],
        'networks'       => [],
    ];

    /**
     * @return mixed[]
     */
    public function config(): array
    {
        $config = $this->config + self::DEFAULT_CONFIG;

        if ($this->flags & self::FLAG_IS_PRODUCTION) {
            unset($config['ports']);
        }

        return $config;
    }
}
