<?php

namespace Spartan\Docker\Service;

use Spartan\Docker\Definition\ServiceAbstract;

class Rabbit extends ServiceAbstract
{
    const VERSION = '3.8';

    const DEFAULT_CONFIG = [
        'image'          => 'rabbitmq:' . self::VERSION . '-alpine',
        'container_name' => '${DOCKER_STACK}_rabbitmq',
        'restart'        => 'always',
        'volumes'        => [
            'rabbit:/var/lib/rabbitmq',
        ],
        'environment'    => [
            'RABBITMQ_DEFAULT_USER=${QUEUE_RABBIT_USER}',
            'RABBITMQ_DEFAULT_PASS=${QUEUE_RABBIT_PASS}',
            'RABBITMQ_DEFAULT_VHOST=${QUEUE_RABBIT_VHOST}',
        ],
        'ports'          => [
            '${DOCKER_PORT}56:15672',
            '${DOCKER_PORT}57:5672',
        ],
    ];

    /**
     * @var mixed[]
     */
    protected array $externalVolumes = [
        'rabbit' => ['driver' => 'local'],
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

        if ($this->flags & self::FLAG_WITH_MANAGEMENT) {
            $config['image'] = 'rabbitmq:' . self::VERSION . '-management-alpine';
        }

        return $config;
    }
}
