<?php

namespace Spartan\Docker\Service;

use Spartan\Docker\Definition\ServiceAbstract;

class PhpCli extends ServiceAbstract
{
    const DEFAULT_CONFIG = [
        'build'          => [
            'context'    => '.',
            'dockerfile' => 'php_cli.dockerfile',
        ],
        'container_name' => '${DOCKER_STACK}_cli',
        'restart'        => 'always',
        'working_dir'    => '/var/www',
        'volumes'        => [
            self::PATH_TO_ROOT . ':/var/www',
        ],
    ];

    /**
     * @return mixed[]
     */
    public function config(): array
    {
        $config = $this->config + self::DEFAULT_CONFIG;

        if ($this->flags & self::FLAG_IS_PRODUCTION) {
            $config['build']['dockerfile'] = 'php_cli.production.dockerfile';
        }

        return $config;
    }
}
