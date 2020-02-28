<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class DebugConfigResolverCommand extends Command
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    private $siteAccess;

    public function __construct(
        ConfigResolverInterface $configResolver,
        SiteAccess $siteAccess
    ) {
        $this->configResolver = $configResolver;
        $this->siteAccess = $siteAccess;

        parent::__construct();
    }

    /**
     * {@inheritdoc}.
     */
    public function configure()
    {
        $this->setName('ezplatform:debug:config-resolver');
        $this->setAliases(['ezplatform:debug:config']);
        $this->setDescription('Debugs / Retrieves a parameter from the Config Resolver');
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
            'Set another scope (SiteAccess) to use. This is an alternative to using the global --siteaccess[=SITEACCESS] option.'
        );
        $this->addOption(
            'namespace',
            null,
            InputOption::VALUE_REQUIRED,
            'Set a different namespace than the default "ezsettings" used by SiteAccess settings.'
        );
        $this->setHelp(<<<EOM
Outputs a given config resolver parameter, more commonly known as a SiteAccess setting.

By default it will give value depending on the global <comment>--siteaccess[=SITEACCESS]</comment> (default SiteAccess is used if not set).

However, you can also manually set <comment>--scope[=NAME]</comment> yourself if you don't want to affect the SiteAccess
set by the system. You can also override the namespace to get something other than the default "ezsettings" namespace by using
the <comment>--namespace[=NS]</comment> option.

NOTE: To see *all* compiled SiteAccess settings, use: <comment>debug:config ezpublish [system.default]</comment>

EOM
        );
    }

    /**
     * {@inheritdoc}.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parameter = $input->getArgument('parameter');
        $namespace = $input->getOption('namespace');
        $scope = $input->getOption('scope');
        $parameterData = $this->configResolver->getParameter($parameter, $namespace, $scope);

        // In case of json output return early with no newlines and only the parameter data
        if ($input->getOption('json')) {
            $output->write(json_encode($parameterData));

            return 0;
        }

        $output->writeln('<comment>SiteAccess name:</comment> ' . $this->siteAccess->name);

        $output->writeln('<comment>Parameter:</comment>');
        $cloner = new VarCloner();
        $dumper = new CliDumper();
        $output->write(
            $dumper->dump(
                $cloner->cloneVar($parameterData),
                true
            )
        );
        return 0;
    }
}
