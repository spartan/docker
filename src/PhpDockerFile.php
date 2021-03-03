<?php

namespace Spartan\Docker;

class PhpDockerFile
{
    const VERSION_74 = '7.4.13';
    const VERSION_80 = '8.0.0';

    protected string $version;

    protected string $mode;

    /**
     * @var mixed[]
     */
    protected array $modules = [];

    /**
     * PhpDockerFile constructor.
     *
     * @param string  $version
     * @param mixed[] $modules
     * @param string  $mode
     */
    public function __construct(string $version = self::VERSION_74, array $modules = [], string $mode = 'fpm')
    {
        $this->version = $version;
        $this->modules = $modules;
        $this->mode    = $mode;
    }

    /**
     * @param mixed[] $modules
     *
     * @return $this
     */
    public function withModules(array $modules): self
    {
        $this->modules = array_unique(
            [
                ...$this->modules,
                ...$modules,
            ]
        );

        return $this;
    }

    /**
     * @param string $module
     * @param string $version
     *
     * @return string[]
     */
    public static function module(string $module, string $version = self::VERSION_74): array
    {
        /*
         * Resources
         *
         * - https://github.com/docker-library/php/issues/926
         */

        $modules = [
            'apcu'      => [
                '# https://pecl.php.net/package/apcu',
                'pecl install apcu-5.1.18',
                'docker-php-ext-enable apcu --ini-name docker-php-ext-10-apcu.ini',
            ],
            'bz2'       => [
                'apt-get -y install libzip-dev libz-dev libbz2-dev',
                'docker-php-ext-install bz2',
            ],
            'gd'        => [
                'apt-get install -y libwebp-dev libjpeg-dev libpng-dev libfreetype6-dev',
                'docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp',
                'docker-php-ext-install -j$(nproc) gd',
            ],
            'imagick'   => [
                'apt-get install -y libmagic-dev',
                'pecl install imagick',
                'docker-php-ext-enable imagick',
            ],
            'memcached' => [
                '# https://pecl.php.net/package/memcached',
                'apt-get install -y --no-install-recommends libmemcached-dev',
                'pecl install memcached-3.1.5',
                'docker-php-ext-enable memcached',
            ],
            'amqp'      => [
                '# https://pecl.php.net/package/amqp',
                'apt-get install -y librabbitmq-dev libssl-dev',
                'pecl install amqp-1.10.2',
                'docker-php-ext-enable amqp',
            ],
            'redis'     => [
                '# https://pecl.php.net/package/redis',
                'apt-get -y install redis-tools',
                'pecl install redis-5.2.2',
                'docker-php-ext-enable redis',
            ],
            'tidy'      => [
                'apt-get install -y libtidy-dev',
                'docker-php-ext-install tidy',
            ],
            'maxmind'   => [
                'apt-get install -y libmaxminddb0',
            ],
            'pdo_mysql' => [
                'docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd',
                'docker-php-ext-install -j$(nproc) pdo_mysql',
            ],
            'mysqli'    => [
                'docker-php-ext-configure mysqli --with-mysqli=mysqlnd',
                'docker-php-ext-install -j$(nproc) mysqli',
            ],
            'xdebug'    => [
                'pecl install xdebug',
                'docker-php-ext-enable xdebug',
                '# echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini',
                '# echo "xdebug.remote_autostart=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini',
                '# echo "xdebug.default_enable=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini',
                '# echo "xdebug.remote_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini',
                '# echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini',
                '# echo "xdebug.remote_connect_back=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini',
                '# echo "xdebug.profiler_enable=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini',
                '# echo "xdebug.remote_log=\"/tmp/xdebug.log\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini',
            ],
            'xhprof'    => [
                'pecl install xhprof-2.2.0',
                'docker-php-ext-enable xhprof',
            ],
            'opcache'   => [
                '# https://stackoverflow.com/questions/59300007/how-can-i-enable-opcache-preloading-in-php-7-4',
                'docker-php-ext-install -j$(nproc) opcache',
                '# echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache-recommended.ini',
                '# echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache-recommended.ini',
                '# echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache-recommended.ini',
                '# echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache-recommended.ini',
                '# echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache-recommended.ini',
            ],
        ];

        return $modules[$module] ?? [
                "#{$module}",
                "docker-php-ext-install {$module}",
            ];
    }

    public function __toString()
    {
        $tab = '    ';

        $file = [
            'RUN apt-get update -y',
            $tab . 'apt-get -y install --no-install-recommends whiptail libtidy-dev apt-utils libicu-dev git gcc make autoconf libc-dev pkg-config libzip-dev',
        ];

        foreach ($this->modules as $module) {
            $moduleLines = self::module($module);
            foreach ($moduleLines as $line) {
                $file[] = $tab . $line;
            }
        }

        $file = [
            ...$file,
            ...[
                $tab . '# Cleanup',
                $tab . 'apt-get remove -y git',
                $tab . 'apt-get autoremove -y',
                $tab . 'apt-get clean',
                $tab . 'rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*',
            ],
        ];

        $dockerfile = "FROM php:{$this->version}-{$this->mode}-buster" . PHP_EOL
            . PHP_EOL
            . implode(" && \\" . PHP_EOL, $file)
            . PHP_EOL;

        $dockerfile = preg_replace('/^[ ]+#([^&]+) .+/m', '    #$1', $dockerfile);

        return (string)$dockerfile;
    }
}
