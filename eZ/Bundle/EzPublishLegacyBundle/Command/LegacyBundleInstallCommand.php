<?php
/**
 * File containing the LegacyBundleInstallCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Command;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Finder\Finder;

class LegacyBundleInstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName( 'ezpublish:legacybundles:install_extensions' )
            ->addOption( 'copy', null, InputOption::VALUE_NONE, 'Creates copies of the extensions instead of using a symlink' )
            ->addOption( 'relative', null, InputOption::VALUE_NONE, 'Make relative symlinks' )
            ->addOption( 'force', null, InputOption::VALUE_NONE, 'Force overwriting of existing directory (will be removed)' )
            ->setDescription( 'Installs legacy extensions (default: symlink) defined in Symfony bundles into ezpublish_legacy/extensions' )
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> installs <info>legacy extensions</info> stored in a Symfony 2 bundle
into the ezpublish_legacy folder.
EOT
            );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $options = array(
            'copy' => (bool)$input->getOption( 'copy' ),
            'relative' => (bool)$input->getOption( 'relative' ),
            'force' => (bool)$input->getOption( 'force' )
        );

        $legacyExtensionsLocator = $this->getContainer()->get( 'ezpublish_legacy.legacy_bundles.extension_locator' );
        $kernel = $this->getContainer()->get( 'kernel' );
        foreach ( $kernel->getBundles() as $bundle )
        {
            foreach ( $legacyExtensionsLocator->getExtensionDirectories( $bundle->getPath() ) as $extensionDir )
            {
                $output->writeln( '- ' . $this->removeCwd( $extensionDir ) );
                try
                {
                    $target = $this->linkLegacyExtension( $extensionDir, $options );
                    $output->writeln( '  <info>' . ( $options['copy'] ? 'Copied' : 'linked' ) . "</info> to $target</info>" );
                }
                catch ( RuntimeException $e )
                {
                    $output->writeln( '  <error>' . $e->getMessage() . '</error>' );
                }
            }
        }
    }

    /**
     * Links the legacy extension at $path into ezpublish_legacy/extensions
     *
     * @param string $extensionPath Absolute path to a legacy extension folder
     * @param array  $options
     *
     * @throws \RuntimeException If a target link/directory exists and $options[force] isn't set to true
     * @return string The resulting link/directory
     */
    protected function linkLegacyExtension( $extensionPath, array $options = array() )
    {
        $options += array( 'force' => false, 'copy' => false, 'relative' => false );
        $filesystem = $this->getContainer()->get( 'filesystem' );
        $legacyRootDir = rtrim( $this->getContainer()->getParameter( 'ezpublish_legacy.root_dir' ), '/' );

        $relativeExtensionPath = $filesystem->makePathRelative( $extensionPath, realpath( "$legacyRootDir/extension/" ) );
        $targetPath = "$legacyRootDir/extension/" . basename( $extensionPath );

        if ( file_exists( $targetPath ) && $options['copy'] )
        {
            if ( !$options['force'] )
            {
                throw new RuntimeException( "Target directory $targetPath already exists" );
            }
            $filesystem->remove( $targetPath );
        }

        if ( file_exists( $targetPath ) && !$options['copy'] )
        {
            if ( is_link( $targetPath ) )
            {
                $existingLinkTarget = readlink( $targetPath );
                if ( $existingLinkTarget == $extensionPath || $existingLinkTarget == $relativeExtensionPath )
                {
                    return $targetPath;
                }
                else if ( !$options['force'] )
                {
                    throw new RuntimeException( "Target $targetPath already exists with a different target" );
                }
            }
            else
            {
                if ( !$options['force'] )
                {
                    throw new RuntimeException( "Target $targetPath already exists with a different target" );
                }
            }
            $filesystem->remove( $targetPath );
        }

        if ( !$options['copy'] )
        {
            try
            {
                $filesystem->symlink(
                    $options['relative'] ? $relativeExtensionPath : $extensionPath,
                    $targetPath
                );
            }
            catch ( IOException $e )
            {
                $options['copy'] = true;
            }
        }

        if ( $options['copy'] )
        {
            $filesystem->mkdir( $targetPath, 0777 );
            $filesystem->mirror( $extensionPath, $targetPath, Finder::create()->in( $extensionPath ) );
        }

        return $targetPath;
    }

    /**
     * Removes the cwd from $path
     * @param string $path
     */
    private function removeCwd( $path )
    {
        return str_replace( getcwd() . '/', '', $path );
    }
}
