<?php

namespace Spartan\Docker\Service;

use Spartan\Docker\Definition\ServiceAbstract;

class PerconaMongo extends ServiceAbstract
{
    const VERSION = '4';

    const DEFAULT_CONFIG = [
        'image'          => 'percona/percona-server-mongodb:' . self::VERSION,
        'container_name' => '${DOCKER_STACK}_mongo',
        'restart'        => 'always',
        'ports'          => [
            '${DOCKER_PORT}17:27017',
        ],
        'volumes'        => [
            'mongo:/data/db',
        ],
    ];

    /**
     * @var mixed[]
     */
    protected array $externalVolumes = [
        'mongo' => ['driver' => 'local'],
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
