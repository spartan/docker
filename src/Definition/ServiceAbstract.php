<?php

namespace Spartan\Docker\Definition;

abstract class ServiceAbstract implements ServiceInterface
{
    use ConfigTrait;

    protected int $flags = 0;

    /**
     * @var mixed[]
     */
    protected array $externalNetworks = [];

    /**
     * @var mixed[]
     */
    protected array $externalVolumes = [];

    /**
     * ServiceAbstract constructor.
     *
     * @param string  $name
     * @param mixed[] $config
     * @param int     $flags
     */
    public function __construct(string $name, array $config = [], int $flags = 0)
    {
        $this->name   = $name;
        $this->config = $config;
        $this->flags  = $flags;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function withInternalNetwork(string $name): self
    {
        $this->config['networks'][] = $name;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function withExternalNetwork(string $name): self
    {
        $this->externalNetworks[$name] = ['external' => true];

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function externalNetworks(): array
    {
        return $this->externalNetworks;
    }

    /**
     * @return mixed[]
     */
    public function externalVolumes(): array
    {
        return $this->externalVolumes;
    }
}
