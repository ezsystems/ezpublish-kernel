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
            ->setName( 'ezpublish:legacybundle:install' )
            ->addOption( 'symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it' )
            ->addOption( 'relative', null, InputOption::VALUE_NONE, 'Make relative symlinks' )
            ->setDescription( 'Installs legacy extensions defined in Symfony bundles into ezpublish_legacy.' )
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> installs <info>legacy extensions</info> stored in a Symfony 2 bundle
into the ezpublish_legacy folder.
EOT
            );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $kernel = $this->getContainer()->get( 'kernel' );
        foreach ( $kernel->getBundles() as $bundle )
        {
            if ( !$bundle->getContainerExtension() instanceof LegacyBundleExtensionInterface )
            {
                continue;
            }

            foreach ( $this->loadLegacyBundleExtensions( $bundle ) as $extensionDir )
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

    protected function loadLegacyBundleExtensions( Bundle $bundle )
    {
        $return = array();
        foreach ( glob( $bundle->getPath() . "/ezpublish_legacy/*", GLOB_ONLYDIR ) as $directory )
        {
            $return[] = $directory;
        }
        return $return;
    }

    /**
     * Links the legacy extension at $path into ezpublish_legacy/extensions
     * @param string $path Absolute path to a legacy extension folder
     */
    protected function linkLegacyExtension( $extensionPath, $symlink = true, $relative = false )
    {
        $legacyRootDir = rtrim( $this->getContainer()->getParameter( 'ezpublish_legacy.root_dir' ), '/' );
        $filesystem = $this->getContainer()->get( 'filesystem' );

        $targetPath = "$legacyRootDir/extension/" . basename( $extensionPath );
        $filesystem->remove( $targetPath );
        echo "Target path: $targetPath\n";
        if ( $symlink )
        {
            if ( $relative )
            {
                $originDir = $filesystem->makePathRelative( $extensionPath, realpath( $targetPath ) );
            }

            $filesystem->symlink( $originDir, $targetPath );
        }
        else
        {
            $filesystem->mkdir( $targetPath, 0777 );
            // We use a custom iterator to ignore VCS files
            $filesystem->mirror( $extensionPath, $targetPath, Finder::create()->in( $extensionPath ) );
        }
    }
}
