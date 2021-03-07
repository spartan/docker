<?php

namespace Spartan\Docker\Command;

use Spartan\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Compose Command
 *
 * @package Spartan\Docker
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Compose extends Command
{
    /**
     * @return void
     */
    public function configure(): void
    {
        $this->withSynopsis('docker:compose', 'Run docker compose', ['compose'])
             ->withArgument('operation', 'Operation. Check `docker/compose.sh`')
             ->withOption('target', 'Remote name')
             ->withOption('dev', 'Alias for --target=dev')
             ->withOption('test', 'Alias for --target=test')
             ->withOption('qa', 'Alias for --target=qa')
             ->withOption('stage', 'Alias for --target=stage')
             ->withOption('review', 'Alias for --target=review');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \ReflectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = null;

        if ($this->isOptionPresent('target')) {
            $env = $input->getOption('target');
        } else {
            $env = key(
                array_filter(
                    [
                        'dev'    => $this->isOptionPresent('dev'),
                        'test'   => $this->isOptionPresent('test'),
                        'qa'     => $this->isOptionPresent('qa'),
                        'stage'  => $this->isOptionPresent('stage'),
                        'review' => $this->isOptionPresent('review'),
                    ]
                )
            ) ?: 'dev';
        }

        $remote = \Spartan\Provisioner\Command\Remote\Remote::forEnv($env);

        $this->process(
            ['./config/docker/compose.sh', $input->getArgument('operation')],
            $output,
            $remote
        );

        return 0;
    }
}
