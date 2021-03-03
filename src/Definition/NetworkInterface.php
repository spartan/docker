<?php

namespace Spartan\Docker\Definition;

interface NetworkInterface
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
