<?php

namespace Spartan\Docker\Volume;

use Spartan\Docker\Definition\ConfigTrait;
use Spartan\Docker\Definition\VolumeInterface;

class LocalVolume implements VolumeInterface
{
    use ConfigTrait;

    /**
     * LocalVolume constructor.
     *
     * @param string  $name
     * @param mixed[] $config
     */
    public function __construct(string $name, array $config = [])
    {
        $this->name   = $name;
        $this->config = ['driver' => 'local'] + $config;
    }
}
