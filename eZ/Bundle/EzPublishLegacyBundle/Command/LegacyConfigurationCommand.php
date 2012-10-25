<?php
/**
 * File containing the LegacyConfigurationCommand class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Dumper;

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
                )
            )
            ->setDescription( 'Creates the ezpublish 5 configuration based on an existing app/ezpublish_legacy' )
            ->setHelp( <<<EOT
The command <info>%command.name%</info> creates the ezpublish 5 configuration,
based on an existing app/ezpublish_legacy installation.

Settings will be picked based on the default siteaccess.
EOT
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $package = $input->getArgument( 'package' );
        $adminSiteaccess = $input->getArgument( 'adminsiteaccess' );

        /** @var $configurationConverter \eZ\Bundle\EzPublishLegacyBundle\SetupWizard\ConfigurationConverter */
        $configurationConverter = $this->getContainer()->get( 'ezpublish_legacy.setup_wizard.configuration_converter' );

        /** @var $kernel */
        $kernel = $this->getContainer()->get( 'kernel' );

        $configurationFile = $kernel->getRootdir() . '/config/ezpublish_' . $kernel->getEnvironment(). '.yml';
        $yamlConfiguration = $configurationConverter->fromLegacy( $package, $adminSiteaccess );
        $dumper = new Dumper();
        file_put_contents(
            $configurationFile,
            $dumper->dump( $yamlConfiguration, 7 )
        );

        $output->writeln( "Configuration written to $configurationFile" );
    }
}
