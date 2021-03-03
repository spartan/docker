<?php

namespace Spartan\Docker\Service;

use Spartan\Docker\Definition\ServiceAbstract;

class Redis extends ServiceAbstract
{
    const VERSION = '5';

    const DEFAULT_CONFIG = [
        'image'          => 'redis:' . self::VERSION . '-alpine',
        'container_name' => '${DOCKER_STACK}_redis',
        'restart'        => 'always',
        'command'        => ["redis-server", "--appendonly", "yes", "--requirepass", '${REDIS_PASS}'],
        'volumes'        => 'redis:/data',
    ];

    /**
     * @var mixed[]
     */
    protected array $externalVolumes = [
        'redis' => ['driver' => 'local'],
    ];

    /**
     * @return mixed[]
     */
    public function config(): array
    {
        return $this->config + self::DEFAULT_CONFIG;
    }
}
