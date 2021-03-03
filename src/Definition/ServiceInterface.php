<?php

namespace Spartan\Docker\Definition;

interface ServiceInterface
{
    const FLAG_EXPOSE_NONE     = 1;
    const FLAG_IS_CLUSTER      = 2;
    const FLAG_IS_DEV          = 4;
    const FLAG_IS_PRODUCTION   = 8;
    const FLAG_WITH_MANAGEMENT = 16;

    const PATH_TO_ROOT     = './../../';
    const PATH_TO_ENV_FILE = './../.env';

    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return mixed[]
     */
    public function config(): array;

    /**
     * @return mixed[]
     */
    public function externalNetworks(): array;

    /**
     * @return mixed[]
     */
    public function externalVolumes(): array;
}
