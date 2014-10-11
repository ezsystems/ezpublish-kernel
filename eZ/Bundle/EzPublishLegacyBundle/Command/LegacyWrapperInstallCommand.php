<?php
/**
 * File containing the LegacyWrapperInstallCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Finder\Finder;
use ezcPhpGenerator;
use ezcPhpGeneratorParameter;

class LegacyWrapperInstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName( 'ezpublish:legacy:assets_install' )
            ->setDefinition(
                array(
                    new InputArgument( 'target', InputArgument::OPTIONAL, 'The target directory', 'web' ),
                )
            )
            ->addOption( 'symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it' )
            ->addOption( 'relative', null, InputOption::VALUE_NONE, 'Make relative symlinks' )
            ->setDescription( 'Installs assets from eZ Publish legacy installation and wrapper scripts for front controllers (like index_cluster.php).' )
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> installs <info>assets</info> from eZ Publish legacy installation
and wrapper scripts for <info>front controllers</info> (like <info>index_cluster.php</info>).
<info>Assets folders:</info> Symlinks will be created from your eZ Publish legacy directory (will fall back to hard copy if symbolic link fails)
<info>Front controllers:</info> Wrapper scripts will be generated.
EOT
            );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $targetArg = rtrim( $input->getArgument( 'target' ), '/' );
        if ( !is_dir( $targetArg ) )
        {
            throw new \InvalidArgumentException( sprintf( 'The target directory "%s" does not exist.', $input->getArgument( 'target' ) ) );
        }

        /**
         * @var \Symfony\Component\Filesystem\Filesystem
         */
        $filesystem = $this->getContainer()->get( 'filesystem' );
        $legacyRootDir = rtrim( $this->getContainer()->getParameter( 'ezpublish_legacy.root_dir' ), '/' );

        $output->writeln( sprintf( "Installing eZ Publish legacy assets from $legacyRootDir using the <comment>%s</comment> option", $input->getOption( 'symlink' ) ? 'symlink' : 'hard copy' ) );
        $symlink = $input->getOption( 'symlink' );

        foreach ( array( 'design', 'extension', 'share', 'var' ) as $folder )
        {
            $targetDir = "$targetArg/$folder";
            $originDir = "$legacyRootDir/$folder";
            $filesystem->remove( $targetDir );
            if ( $symlink )
            {
                if ( $input->getOption( 'relative' ) )
                {
                    $originDir = $filesystem->makePathRelative( $originDir, realpath( $targetArg ) );
                }

                try
                {
                    $filesystem->symlink( $originDir, $targetDir );
                }
                catch ( IOException $e )
                {
                    $symlink = false;
                    $output->writeln( 'It looks like your system doesn\'t support symbolic links, so will fallback to hard copy instead!' );
                }
            }

            if ( !$symlink )
            {
                $filesystem->mkdir( $targetDir, 0777 );
                // We use a custom iterator to ignore VCS files
                $filesystem->mirror( $originDir, $targetDir, Finder::create()->in( $originDir ) );
            }
        }

        if ( $input->getOption( 'relative' ) )
        {
            $legacyRootDir = $filesystem->makePathRelative( realpath( $legacyRootDir ), realpath( $targetArg ) );
        }

        $output->writeln( "Installing wrappers for eZ Publish legacy front controllers (rest & cluster) with path $legacyRootDir" );
        foreach ( array( 'index_rest.php', 'index_cluster.php' ) as $frontController )
        {
            $newFrontController = "$targetArg/$frontController";
            $filesystem->remove( $newFrontController );
            $generator = new ezcPhpGenerator( $newFrontController, false );
            $generator->lineBreak = "\n";
            $generator->appendCustomCode(
                <<<EOT
<?php
/**
 * File containing the wrapper around the legacy $frontController file
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
EOT
            );
            $generator->appendValueAssignment( 'legacyRoot', $legacyRootDir );
            $generator->appendFunctionCall(
                'chdir',
                array(
                    new ezcPhpGeneratorParameter( 'legacyRoot' )
                )
            );
            $generator->appendCustomCode( "require \$legacyRoot . '/$frontController';" );
            $generator->appendEmptyLines();
            $generator->finish();
        }
    }
}
