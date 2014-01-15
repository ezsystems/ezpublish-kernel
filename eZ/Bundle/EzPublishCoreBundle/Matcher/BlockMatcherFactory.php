<?php
/**
 * File containing the BlockMatcherFactory class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Matcher;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Matcher\BlockMatcherFactory as BaseFactory;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlockMatcherFactory extends BaseFactory implements SiteAccessAware, ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface;
     */
    private $configResolver;

    public function __construct( ConfigResolverInterface $configResolver, Repository $repository )
    {
        $this->configResolver = $configResolver;
        parent::__construct(
            $repository,
            $this->configResolver->getParameter( 'block_view' )
        );
    }

    public function setContainer( ContainerInterface $container = null )
    {
        $this->container = $container;
    }

    /**
     * @param string $matcherIdentifier
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MatcherInterface
     */
    protected function getMatcher( $matcherIdentifier )
    {
        if ( $this->container->has( $matcherIdentifier ) )
            return $this->container->get( $matcherIdentifier );

        return parent::getMatcher( $matcherIdentifier );
    }

    /**
     * Changes internal configuration to use the one for passed SiteAccess.
     *
     * @param SiteAccess $siteAccess
     */
    public function setSiteAccess( SiteAccess $siteAccess = null )
    {
        if ( $siteAccess === null )
        {
            return;
        }

        $this->matchConfig = $this->configResolver->getParameter( 'block_view', 'ezsettings', $siteAccess->name );
    }
}
