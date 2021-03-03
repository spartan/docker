<?php

namespace Spartan\Docker\Command;

use Spartan\Console\Command;
use Spartan\Docker\ComposeFile;
use Spartan\Docker\PhpDockerFile;
use Spartan\Docker\Service\ElasticSearch;
use Spartan\Docker\Service\Influx;
use Spartan\Docker\Service\Maria;
use Spartan\Docker\Service\Memcached;
use Spartan\Docker\Service\Mysql;
use Spartan\Docker\Service\Nginx;
use Spartan\Docker\Service\PerconaMongo;
use Spartan\Docker\Service\PerconaMysql;
use Spartan\Docker\Service\PhpCli;
use Spartan\Docker\Service\PhpFpm;
use Spartan\Docker\Service\PhpZts;
use Spartan\Docker\Service\Postgres;
use Spartan\Docker\Service\Rabbit;
use Spartan\Docker\Service\Redis;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Init Command
 *
 * @package Spartan\Docker
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Init extends Command
{
    /**
     * @return void
     */
    public function configure(): void
    {
        $this->withSynopsis('docker:init', 'Init docker configuration files', ['init']);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadEnv();

        $src = __DIR__ . '/../../config';
        $dst = getcwd() . '/config/docker';

        if (!file_exists($dst)) {
            mkdir($dst, 0777, true);
        }

        /*
         * Docker services
         */

        $this->panel(
            "Setup Docker for PHP project

> Requirements - You need `docker` and `docker-compose` installed
> Docker       - Remember to add your user to `docker` group to run without sudo
                 ex: `sudo usermod -aG docker \$USER`
> Services     - You can select multiple services for Docker
                 You can only select one version of the same type!
> Stack        - All services within this project will use a prefix as docker stack name
> Port         - To access services from host you must provide a port prefix.
                 Leave empty to not expose ports.
                 ex: 111 => 11180 for nginx, 11190 for ElasticSearch etc
"
        );

        $services = $this->choose('', self::dockerServices());

        $phpVersions = [
            'php74_fpm' => [PhpDockerFile::VERSION_74, 'fpm'],
            'php80_fpm' => [PhpDockerFile::VERSION_80, 'fpm'],
            'php74_zts' => [PhpDockerFile::VERSION_74, 'fpm'],
            'php80_zts' => [PhpDockerFile::VERSION_80, 'zts'],
            'php74_cli' => [PhpDockerFile::VERSION_74, 'cli'],
            'php80_cli' => [PhpDockerFile::VERSION_80, 'cli'],
        ];

        foreach ($phpVersions as $service => $data) {
            $version = $data[0];
            $mode    = $data[1];

            /*
             * PHP & modules
             */
            if ($this->isServiceSelected($service, $services)) {

                $this->panel(
                    "PHP-{$mode} Docker configuration

> Default - For a list of default modules included run:
            `docker run -it --rm php:7.4-fpm-alpine php -m`
> Modules - Select extra modules to include
"
                );
                $modules = $this->choose('', self::phpModules());

                $dockerfile = new PhpDockerFile($version, $modules, $mode);

                file_put_contents("{$dst}/php.{$mode}.dockerfile", (string)$dockerfile);
            }
        }

        /*
         * Copy files
         */
        $this->process("cp {$src}/compose.sh {$dst}/compose.sh");

        if ($this->isServiceSelected('nginx', $services)) {
            $this->process("cp {$src}/nginx.conf {$dst}/nginx.conf");
        }

        if ($this->isServiceSelected('mysql', $services)) {
            $this->process("cp {$src}/mysql.cnf {$dst}/mysql.cnf");
        }

        if ($this->isServiceSelected('percona', $services)) {
            $this->process("cp {$src}/percona.cnf {$dst}/percona.cnf");
        }

        /*
         * Generate docker-compose.yml
         */
        $composeFile = $this->dockerCompose($services);
        file_put_contents("{$dst}/docker-compose.yml", $composeFile->toYaml());

        if ($this->isServiceSelected('mysql', $services) || $this->isServiceSelected('percona', $services)) {
            $this->panel(
                "
    Remeber to add the following .env variables:
    - DB_ROOT
    - DB_NAME
    - DB_USER
    - DB_PASS
"
            );
        }

        if ($this->isServiceSelected('influx', $services)) {
            $this->panel(
                "
    Remeber to add the following .env variables:
    - INFLUX_HOST
    - INFLUX_PORT
    - INFLUX_DB
    - INFLUX_ADMIN_USER
    - INFLUX_ADMIN_PASS
    - INFLUX_READ_USER
    - INFLUX_READ_PASS
    - INFLUX_WRITE_USER
    - INFLUX_WRITE_PASS
"
            );
        }

        return 0;
    }

    /**
     * @return array[]
     */
    public static function dockerServices(): array
    {
        return [
            'WEB'     => [
                'nginx'     => 'Nginx',
                'nginx_ssl' => 'Nginx behind proxy w/ LetsEncrypt',
                '_'         => [
                    'max'      => 1,
                    'selected' => 'nginx_ssl',
                ],
            ],
            'PHP-fpm' => [
                'php74_fpm' => 'PHP 7.4-fpm',
                'php80_fpm' => 'PHP 8.0-fpm',
                '_'         => [
                    'max'      => 1,
                    'selected' => 'php74_fpm',
                ],
            ],
            'PHP-zts' => [
                'php74_zts' => 'PHP 7.4-zts',
                'php80_zts' => 'PHP 8.0-zts',
                '_'         => [
                    'max' => 1,
                ],
            ],
            'PHP-cli' => [
                'php74_cli' => 'PHP 7.4-cli',
                'php80_cli' => 'PHP 8.0-cli',
                '_'         => [
                    'max' => 1,
                ],
            ],
            'STORAGE' => [
                'mysql5'         => 'MySQL 5.7 (legacy)',
                'percona5'       => 'Percona MySQL 5.7 (legacy)',
                'mysql8'         => 'MySQL ' . Mysql::VERSION,
                'percona8'       => 'Percona MySQL ' . PerconaMysql::VERSION,
                'maria'          => 'MariaDB ' . Maria::VERSION,
                'elasticsearch7' => 'ElasticSearch ' . ElasticSearch::VERSION,
                'influx1'        => 'InfluxDB ' . Influx::VERSION,
                'postgres'       => 'PostgreSQL ' . Postgres::VERSION,
                'mongo'          => 'MongoDB ' . PerconaMongo::VERSION,
                '_'              => [
                    'selected' => 'percona8',
                ],
            ],
            'CACHE'   => [
                'memcached' => 'Memcached ' . Memcached::VERSION,
                'redis'     => 'Redis ' . Redis::VERSION,
            ],
            'QUEUE'   => [
                'rabbit3' => 'RabbitMQ ' . Rabbit::VERSION,
            ],
        ];
    }

    /**
     * @return array[]
     */
    public static function phpModules(): array
    {
        return [
            "PHP modules" => [
                'apcu'      => 'APCu',
                'bcmath'    => 'BC Math',
                'bz2'       => 'Bzip2',
                'gd'        => 'GD',
                'gettext'   => 'Gettext',
                'gmp'       => 'GMP',
                'intl'      => 'Intl',
                'mysqli'    => 'MySQLi',
                'opcache'   => 'OPCache',
                'pcntl'     => 'PCNTL',
                'pdo_mysql' => 'PDO MySQL',
                'sockets'   => 'Sockets',
                'tidy'      => 'Tidy',
                // Extra
                'memcached' => 'Memcached',
                'amqp'      => 'AMQP',
                'maxmind'   => 'MaxMind',
                'xdebug'    => 'XDebug',
                'xhprof'    => 'Xhprof',
                '_'         => [
                    'selected' => array_filter(
                        [
                            'apcu',
                            'bcmath',
                            'bz2',
                            'gd',
                            'gettext',
                            'intl',
                            'opcache',
                            'mysqli',
                            'pdo_mysql',
                            'sockets',
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public static function serviceMappings(): array
    {
        return [
            'nginx'          => function () {
                return (new Nginx('nginx'))
                    ->withConfig(['environment' => []])
                    ->withExternalNetwork('web')
                    ->withInternalNetwork('default');
            },
            'nginx_ssl'      => function () {
                return (new Nginx('nginx'))
                    ->withExternalNetwork('web')
                    ->withInternalNetwork('default');
            },
            'php74_fpm'      => function () {
                return (new PhpFpm('fpm'))->withInternalNetwork('default');
            },
            'php74_zts'      => function () {
                return (new PhpZts('zts'))->withInternalNetwork('default');
            },
            'php74_cli'      => function () {
                return (new PhpCli('cli'))->withInternalNetwork('default');
            },
            'php80_fpm'      => function () {
                return (new PhpFpm('fpm'))->withInternalNetwork('default');
            },
            'php80_zts'      => function () {
                return (new PhpZts('zts'))->withInternalNetwork('default');
            },
            'php80_cli'      => function () {
                return (new PhpCli('cli'))->withInternalNetwork('default');
            },
            'mysql5'         => function () {
                return (new Mysql('mysql'))
                    ->withConfig(['image' => 'mysql:5.7'])
                    ->withInternalNetwork('default');
            },
            'mysql8'         => function () {
                return (new Mysql('mysql'))->withInternalNetwork('default');
            },
            'percona5'       => function () {
                return (new PerconaMysql('mysql'))
                    ->withConfig(['image' => 'percona/percona-server:5.7'])
                    ->withInternalNetwork('default');
            },
            'percona8'       => function () {
                return (new PerconaMysql('mysql'))->withInternalNetwork('default');
            },
            'maria'          => function () {
                return (new Maria('mysql'))->withInternalNetwork('default');
            },
            'elasticsearch7' => function () {
                return (new ElasticSearch('elastic'))->withInternalNetwork('default');
            },
            'influx1'        => function () {
                return (new Influx('influx'))->withInternalNetwork('default');
            },
            'postgres'       => function () {
                return (new Postgres('postgres'))->withInternalNetwork('default');
            },
            'mongo'          => function () {
                return (new PerconaMongo('mongo'))->withInternalNetwork('default');
            },
            //cache
            'memcached'      => function () {
                return (new Memcached('memcached'))->withInternalNetwork('default');
            },
            'redis'          => function () {
                return (new Redis('redis'))->withInternalNetwork('default');
            },
            // queue
            'rabbit3'        => function () {
                return (new Rabbit('rabbit'))->withInternalNetwork('default');
            },
        ];
    }

    /**
     * @param mixed[] $serviceList
     *
     * @return ComposeFile
     */
    public function dockerCompose(array $serviceList): ComposeFile
    {
        $composeFile = new ComposeFile();

        $mappings = self::serviceMappings();

        foreach ($serviceList as $serviceName) {
            $service = $mappings[$serviceName]();
            $composeFile->withService($service);
        }

        return $composeFile;
    }

    /*
     * Helpers
     */

    /**
     * @param string  $name
     * @param mixed[] $services
     *
     * @return bool
     */
    public function isServiceSelected(string $name, array $services): bool
    {
        foreach ($services as $service) {
            if (preg_match("/^{$name}/", $service)) {
                return true;
            }
        }

        return false;
    }
}
