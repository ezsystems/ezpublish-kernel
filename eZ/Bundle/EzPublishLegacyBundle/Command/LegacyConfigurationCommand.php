<?php
/**
 * File containing the LegacyConfigurationCommand class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    eZ\Publish\Core\MVC\Symfony\ConfigDumperInterface;

class LegacyConfigurationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName( 'ezpublish:configure' )
            ->setDefinition(
                array(
                    new InputArgument( 'package', InputArgument::REQUIRED, 'Name of the installed package. Used to generate the settings group name. Example: ezdemo_site' ),
                    new InputArgument( 'adminsiteaccess', InputArgument::REQUIRED, 'Name of your admin siteaccess. Example: ezdemo_site_admin' ),
                    new InputOption( 'backup', null, InputOption::VALUE_NONE, 'Makes a backup of existing files if any' ),
                )
            )
            ->setDescription( 'Creates the ezpublish 5 configuration based on an existing ezpublish_legacy' )
            ->setHelp( <<<EOT
The command <info>%command.name%</info> creates the ezpublish 5 configuration,
based on an existing ezpublish_legacy installation.

Settings will be picked based on the default siteaccess.
EOT
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $package = $input->getArgument( 'package' );
        $adminSiteaccess = $input->getArgument( 'adminsiteaccess' );
        $kernel = $this->getContainer()->get( 'kernel' );

        /** @var $configurationConverter \eZ\Bundle\EzPublishLegacyBundle\SetupWizard\ConfigurationConverter */
        $configurationConverter = $this->getContainer()->get( 'ezpublish_legacy.setup_wizard.configuration_converter' );
        /** @var $configurationDumper \eZ\Bundle\EzpublishLegacyBundle\SetupWizard\ConfigurationDumper */
        $configurationDumper = $this->getContainer()->get( 'ezpublish_legacy.setup_wizard.configuration_dumper' );
        $configurationDumper->addEnvironment( $kernel->getEnvironment() );

        $options = ConfigDumperInterface::OPT_DEFAULT;
        if ( $input->getOption( 'backup' ) )
            $options |= ConfigDumperInterface::OPT_BACKUP_CONFIG;
        $configurationDumper->dump( $configurationConverter->fromLegacy( $package, $adminSiteaccess ), $options );

        $output->writeln( "Configuration written to ezpublish.yml and environment related ezpublish configuration files." );
    }
}
