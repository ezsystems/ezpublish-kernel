<?php
/**
 * File containing the LegacyEmbedScriptCommand class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use ezcPhpGenerator;
use ezcPhpGeneratorParameter;

class LegacyEmbedScriptCommand extends ContainerAwareCommand
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    protected function configure()
    {
        $this
            ->setName( 'ezpublish:legacy:script' )
            ->addArgument( 'script', InputArgument::REQUIRED, 'Path to legacy script you want to run. Path must be relative to the legacy root' )
            ->addOption( 'legacy-help', null, InputOption::VALUE_NONE, 'Use this option if you want to display help for the legacy script' )
            ->setDescription( 'Runs an eZ Publish legacy script.' )
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> runs a <info>legacy script</info>.
Passed <info>script</info> argument must be relative to eZ Publish legacy root (e.g. bin/php/eztc.php, extension/myextension/bin/php/myscript.php).
EOT
            );

        // Ignore validation errors to avoid exceptions due to non declared options/arguments (those passed to the legacy script)
        $this->ignoreValidationErrors();
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $this->container = $this->getContainer();
        $legacyScript = $input->getArgument( 'script' );

        // Cleanup the input arguments as the legacy kernel expects the script to run as first argument
        foreach ( $_SERVER['argv'] as $rawArg )
        {
            if ( $rawArg === $legacyScript )
                break;

            array_shift( $_SERVER['argv'] );
            array_shift( $GLOBALS['argv'] );
        }

        if ( $input->getOption( 'legacy-help' ) )
        {
            $_SERVER['argv'][] = '--help';
            $GLOBALS['argv'][] = '--help';
        }

        $output->writeln( "<comment>Running script '$legacyScript' in eZ Publish legacy context</comment>" );

        /** @var $legacyCLIHandlerClosure \Closure */
        $legacyCLIHandlerClosure = $this->container->get( 'ezpublish_legacy.kernel_handler.cli' );
        /** @var $legacyKernelClosure \Closure */
        $legacyKernelClosure = $this->container->get( 'ezpublish_legacy.kernel' );

        // CLIHandler is contained in $legacyKernel, but we need to inject the script to run separately.
        $legacyCLIHandlerClosure()->setEmbeddedScriptPath( $legacyScript );
        $legacyKernelClosure()->run();
    }
}
