<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DebugServiceCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}.
     */
    public function configure()
    {
        $this->setName('ezplatform:debug:service');
        $this->setDescription('Debug / Retrive class name of a service');
        $this->addArgument(
            'service',
            InputArgument::REQUIRED,
            'The service to return class name for, for instance "ezpublish.cache_pool"'
        );
        $this->addOption(
            'oneline',
            'o',
            InputOption::VALUE_NONE,
            'Only return value, for automation / testing use on a single line.'
        );
        $this->setHelp(<<<EOM
Outputs a given service class name.

To rather see service definition, use: <comment>debug:container ezpublish.cache_pool</comment>

EOM
        );
    }

    /**
     * {@inheritdoc}.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $service = $input->getArgument('service');
        $className = get_class($this->getContainer()->get($service));
        if ($input->getOption('oneline')) {
            $output->write($className);
        } else {
            $output->writeln("<comment>Class name:</comment> ".$className);
        }
    }
}
