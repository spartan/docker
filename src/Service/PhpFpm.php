<?php

namespace Spartan\Docker\Service;

use Spartan\Docker\Definition\ServiceAbstract;

class PhpFpm extends ServiceAbstract
{
    const DEFAULT_CONFIG = [
        'build'          => [
            'context'    => '.',
            'dockerfile' => 'php_fpm.dockerfile',
        ],
        'container_name' => '${DOCKER_STACK}_fpm',
        'restart'        => 'always',
        'working_dir'    => '/var/www',
        'volumes'        => [
            self::PATH_TO_ROOT . ':/var/www',
        ],
    ];

    /**
     * @var mixed[]
     */
    public function config(): array
    {
        $config = $this->config + self::DEFAULT_CONFIG;

        if ($this->flags & self::FLAG_IS_PRODUCTION) {
            $config['build']['dockerfile'] = 'php_fpm.production.dockerfile';
        }

        return $config;
    }
}
