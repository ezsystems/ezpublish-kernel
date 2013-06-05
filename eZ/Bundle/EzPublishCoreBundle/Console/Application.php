<?php
/**
 * File containing the Application class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Console;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * eZ Publish console application.
 * Adds options specific to an eZ Publish environment, such as the siteaccess to use
 */
class Application extends BaseApplication
{
    /**
     * @var string
     */
    private $siteAccessName;

    public function __construct( KernelInterface $kernel )
    {
        parent::__construct( $kernel );
        $this->getDefinition()->addOption(
            new InputOption( '--siteaccess', null, InputOption::VALUE_OPTIONAL, 'SiteAccess to use for operations. If not provided, default siteaccess will be used' )
        );
    }

    public function doRun( InputInterface $input, OutputInterface $output )
    {
        $this->siteAccessName = $input->getParameterOption( '--siteaccess', null );
        return parent::doRun( $input, $output );
    }

    protected function registerCommands()
    {
        parent::registerCommands();

        $container = $this->getKernel()->getContainer();
        $this->siteAccessName = $this->siteAccessName ?: $container->getParameter( 'ezpublish.siteaccess.default' );
        $siteAccess = new SiteAccess( $this->siteAccessName, 'cli' );
        $container->set( 'ezpublish.siteaccess', $siteAccess );

        // Replacing legacy kernel handler web by the CLI one
        // @todo: this should be somewhat done in the legacy bundle
        $legacyHandlerCLI = $container->get( 'ezpublish_legacy.kernel_handler.cli' );
        $container->set( 'ezpublish_legacy.kernel_handler', $legacyHandlerCLI );
        $container->set( 'ezpublish_legacy.kernel_handler.web', $legacyHandlerCLI );
    }

}
