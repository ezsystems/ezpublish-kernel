<?php
/**
 * Contains: PSR-0 [Class]Loader Class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base;

use eZ\Bundle\EzPublishCoreBundle\EzPublishCoreBundle;
use eZ\Bundle\EzPublishLegacyBundle\EzPublishLegacyBundle;
use eZ\Bundle\EzPublishRestBundle\EzPublishRestBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use eZ\Publish\API\Container;



class TestKernel extends Kernel implements Container
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct( 'test', true );
        $this->loadClassCache();
        $this->boot();
    }

    /**
     * Returns an array of bundles to registers.
     *
     * @return array An array of bundle instances.
     *
     * @api
     */
    public function registerBundles()
    {
        $bundles = array(
            new FrameworkBundle(),
            new SecurityBundle(),
            new TwigBundle(),
            //new SensioGeneratorBundle(),
            new EzPublishCoreBundle(),
            new EzPublishLegacyBundle(),
            new EzPublishRestBundle()
        );

        return $bundles;
    }

    /**
     * Loads the container configuration
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     *
     * @api
     */
    public function registerContainerConfiguration( LoaderInterface $loader )
    {
        $loader->load( __DIR__ . '/config/config.yml' );
    }

    /**
     * Get Repository object
     *
     * Public API for
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository()
    {
        return $this->getContainer()->get( 'ezpublish.api.repository' );
    }
}
