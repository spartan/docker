<?php

namespace Spartan\Docker\Test;

use PHPUnit\Framework\TestCase;
use Spartan\Docker\PhpDockerFile;

class PhpBusterTest extends TestCase
{
    public function testBuster()
    {
        $df = new PhpDockerFile(PhpDockerFile::VERSION_74, ['apcu'], 'fpm');

        $this->assertSame(trim((string)$df), <<<EOT
FROM php:7.4.13-fpm-buster

RUN apt-get update -y && \
    apt-get -y install --no-install-recommends whiptail libtidy-dev apt-utils libicu-dev git gcc make autoconf libc-dev pkg-config libzip-dev && \
    # https://pecl.php.net/package/apcu
    pecl install apcu-5.1.18 && \
    docker-php-ext-enable apcu --ini-name docker-php-ext-10-apcu.ini && \
    # Cleanup
    apt-get remove -y git && \
    apt-get autoremove -y && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
EOT
);
    }
}
