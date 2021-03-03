<?php

namespace Spartan\Docker\Definition;

interface VolumeInterface
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return mixed[]
     */
    public function config(): array;
}
