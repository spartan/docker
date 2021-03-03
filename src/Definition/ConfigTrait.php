<?php

namespace Spartan\Docker\Definition;

trait ConfigTrait
{
    protected string $name;

    /**
     * @var mixed[]
     */
    protected array $config = [];

    /**
     * ConfigTrait constructor.
     *
     * @param string  $name
     * @param mixed[] $config
     */
    public function __construct(string $name, array $config = [])
    {
        $this->name   = $name;
        $this->config = $config;
    }

    /**
     * @param mixed[] $config
     *
     * @return $this
     */
    public function withConfig(array $config): self
    {
        $this->config = array_replace_recursive($this->config, $config);

        return $this;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    public function config(): array
    {
        return $this->config;
    }
}
