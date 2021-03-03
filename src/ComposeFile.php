<?php

namespace Spartan\Docker;

use Spartan\Docker\Definition\NetworkInterface;
use Spartan\Docker\Definition\ServiceInterface;
use Spartan\Docker\Definition\VolumeInterface;
use Symfony\Component\Yaml\Yaml;

class ComposeFile
{
    const VERSION = '3.9';

    const SUPPORTED_VERSIONS = ['3.5', '3.6', '3.7', '3.8', '3.9'];

    protected string $version = self::VERSION;

    /**
     * @var mixed[]
     */
    protected array $services = [];

    /**
     * @var mixed[]
     */
    protected array $networks = [];

    /**
     * @var mixed[]
     */
    protected array $volumes = [];

    /**
     * @param string $version
     *
     * @return $this
     */
    public function withVersion(string $version): self
    {
        if (!in_array($version, self::SUPPORTED_VERSIONS)) {
            throw new \InvalidArgumentException('Unsupported version: ' . $version);
        }

        $this->version = $version;

        return $this;
    }

    /**
     * @param ServiceInterface $service
     *
     * @return $this
     */
    public function withService(ServiceInterface $service): self
    {
        $this->services[$service->name()] = $service->config();
        $this->networks                   += $service->externalNetworks();
        $this->volumes                    += $service->externalVolumes();

        return $this;
    }

    /**
     * @param NetworkInterface $network
     *
     * @return $this
     */
    public function withNetwork(NetworkInterface $network): self
    {
        $this->networks[$network->name()] = $network->config();

        return $this;
    }

    /**
     * @param VolumeInterface $volume
     *
     * @return $this
     */
    public function withVolume(VolumeInterface $volume): self
    {
        $this->volumes[$volume->name()] = $volume->config();

        return $this;
    }

    /**
     * @return string
     */
    public function toYaml(): string
    {
        $config = [
            'version'  => $this->version,
            'services' => $this->services,
            'networks' => $this->networks,
            'volumes'  => $this->volumes,
        ];

        $yaml = Yaml::dump(array_filter($config), 10);

        return Yaml::dump(Yaml::parse($yaml), 10);
    }
}
