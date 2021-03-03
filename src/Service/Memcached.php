<?php

namespace Spartan\Docker\Service;

use Spartan\Docker\Definition\ServiceAbstract;

class Memcached extends ServiceAbstract
{
    const VERSION = '1.6';

    const DEFAULT_CONFIG = [
        'image'          => 'memcached:' . self::VERSION . '-alpine',
        'container_name' => '${DOCKER_STACK}_memcached',
        'restart'        => 'always',
    ];

    /**
     * @return mixed[]
     */
    public function config(): array
    {
        return $this->config + self::DEFAULT_CONFIG;
    }
}
