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
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class DebugConfigResolverCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}.
     */
    public function configure()
    {
        $this->setName('ezplatform:debug:config-resolver');
        $this->setAliases(['ezplatform:debug:config']);
        $this->setDescription('Debug / Retrive parameter from Config Resolver');
        $this->addArgument(
            'parameter',
            InputArgument::REQUIRED,
            'The configuration resolver parameter to return, for instance "languages" or "http_cache.purge_servers"'
        );
        $this->addOption(
            'json',
            null,
            InputOption::VALUE_NONE,
            'Only return value, for automation / testing use on a single line in json format.'
        );
        $this->addOption(
            'scope',
            null,
            InputOption::VALUE_REQUIRED,
            'Set another scope (siteaccess) to use, alternative to usiong the global --siteaccess[=SITEACCESS] option.'
        );
        $this->addOption(
            'namespace',
            null,
            InputOption::VALUE_REQUIRED,
            'Set another namespace then default "ezsettings" used by siteaccess settings.'
        );
        $this->setHelp(<<<EOM
Outputs a given config resolver parameter, more commonly known as a SiteAccess setting.

By default it will give value depending on global <comment>--siteaccess[=SITEACCESS]</comment> (default siteaccess is used if not set).

However you can also manually set <comment>--scope[=NAME]</comment> yourself if you don't want to affect the siteaccess
set by the system. You can also override namespace to get something else than default "ezsettings" namespace using
<comment>--namespace[=NS]</comment> option.

NOTE: To rather see *all* compiled siteaccess settings, use: <comment>debug:config ezpublish [system.default]</comment>

EOM
        );
    }

    /**
     * {@inheritdoc}.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver */
        $configResolver = $this->getContainer()->get('ezpublish.config.resolver');
        $parameter = $input->getArgument('parameter');
        $namespace = $input->getOption('namespace');
        $scope = $input->getOption('scope');
        $parameterData = $configResolver->getParameter($parameter, $namespace, $scope);

        if ($input->getOption('json')) {
            $output->write(json_encode($parameterData));

            return;
        }

        /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess */
        $siteAccess = $this->getContainer()->get('ezpublish.siteaccess');
        $output->writeln('<comment>SiteAccess name:</comment> ' . $siteAccess->name);

        $output->writeln("<comment>Parameter:</comment>");
        $cloner = new VarCloner();
        $dumper = new CliDumper();
        $output->write(
            $dumper->dump(
                $cloner->cloneVar($parameterData),
                true
            )
        );
    }
}
