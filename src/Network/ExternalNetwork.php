<?php

namespace Spartan\Docker\Volume;

use Spartan\Docker\Definition\ConfigTrait;
use Spartan\Docker\Definition\NetworkInterface;

class ExternalNetwork implements NetworkInterface
{
    use ConfigTrait;

    /**
     * ExternalNetwork constructor.
     *
     * @param string  $name
     * @param mixed[] $config
     */
    public function __construct(string $name, array $config = [])
    {
        $this->name   = $name;
        $this->config = ['external' => true] + $config;
    }
}
