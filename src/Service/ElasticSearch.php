<?php

namespace Spartan\Docker\Service;

use Spartan\Docker\Definition\ServiceAbstract;

class ElasticSearch extends ServiceAbstract
{
    const VERSION = '7.6.2';

    const DEFAULT_CONFIG = [
        'image'          => 'docker.elastic.co/elasticsearch/elasticsearch:' . self::VERSION,
        'container_name' => '${DOCKER_STACK}_elasticsearch',
        'restart'        => 'always',
        'environment'    => [
            'cluster.name=${DOCKER_STACK}_elasticsearch',
            'discovery.type=single-node',
            'bootstrap.memory_lock=true',
            'xpack.security.enabled=false',
            'ES_JAVA_OPTS=-Xms1024m -Xmx1024m',
        ],
        'ulimits'        => [
            'memlock' => [
                'soft' => -1,
                'hard' => -1,
            ],
        ],
        'volumes'        => [
            "elastic:/usr/share/elasticsearch/data",
        ],
        'ports'          => [
            '${DOCKER_PORT}92:9200',
        ],
    ];

    /**
     * @var mixed[]
     */
    protected array $externalVolumes = [
        'elastic' => ['driver' => 'local'],
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
