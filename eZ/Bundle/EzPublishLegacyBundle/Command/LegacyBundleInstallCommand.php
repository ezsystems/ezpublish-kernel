<?php
/**
 * File containing the LegacyBundleInstallCommand class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Command;

use eZ\Bundle\EzPublishLegacyBundle\LegacyBundleExtensionInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use ezcPhpGenerator;
use ezcPhpGeneratorParameter;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LegacyBundleInstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName( 'ezpublish:legacybundles:install_extensions' )
            ->addOption( 'symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it' )
            ->addOption( 'relative', null, InputOption::VALUE_NONE, 'Make relative symlinks' )
            ->setDescription( 'Installs (symlink/copy) legacy extensions defined in Symfony bundles into ezpublish_legacy.' )
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> installs <info>legacy extensions</info> stored in a Symfony 2 bundle
into the ezpublish_legacy folder.
EOT
            );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $legacyExtensionsLocator = $this->getContainer()->get( 'ezpublish_legacy.legacy_bundles.extension_locator' );

        $kernel = $this->getContainer()->get( 'kernel' );
        foreach ( $kernel->getBundles() as $bundle )
        {
            foreach ( $legacyExtensionsLocator->getExtensionDirectories( $bundle->getPath() ) as $extensionDir )
            {
                $output->writeln( $extensionDir );
                $this->linkLegacyExtension(
                    $extensionDir,
                    $input->getOption( 'symlink' ),
                    $input->getOption( 'relative' )
                );
            }
        }
    }

    /**
     * Links the legacy extension at $path into ezpublish_legacy/extensions
     * @param string $extensionPath Absolute path to a legacy extension folder
     * @param bool $symlink
     * @param bool $relative
     */
    protected function linkLegacyExtension( $extensionPath, $symlink = true, $relative = false )
    {
        $legacyRootDir = rtrim( $this->getContainer()->getParameter( 'ezpublish_legacy.root_dir' ), '/' );
        $filesystem = $this->getContainer()->get( 'filesystem' );

        $targetPath = "$legacyRootDir/extension/" . basename( $extensionPath );
        $filesystem->remove( $targetPath );
        if ( $symlink )
        {
            if ( $relative )
            {
                $extensionPath = $filesystem->makePathRelative( $extensionPath, realpath( "$legacyRootDir/extension/" ) );
            }

            $filesystem->symlink( $extensionPath, $targetPath );
        }
        else
        {
            $filesystem->mkdir( $targetPath, 0777 );
            $filesystem->mirror( $extensionPath, $targetPath, Finder::create()->in( $extensionPath ) );
        }
    }
}
