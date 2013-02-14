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

        // Cleanup the input arguments as the legacy kernel expects the script to run as first argument
        for ( $i = 0; $i < 2; ++$i )
        {
            array_shift( $GLOBALS['argv'] );
            array_shift( $_SERVER['argv'] );
        }

        /** @var $legacyCLIHandlerClosure \Closure */
        $legacyCLIHandlerClosure = $this->container->get( 'ezpublish_legacy.kernel_handler.cli' );
        /** @var $legacyKernelClosure \Closure */
        $legacyKernelClosure = $this->container->get( 'ezpublish_legacy.kernel' );

        $legacyCLIHandlerClosure()->setEmbeddedScript( $input->getArgument( 'script' ) );
        $legacyKernelClosure()->run();
    }
}
