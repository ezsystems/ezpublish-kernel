<?php
/**
 * File containing the View\Provider\Content\Configured class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\RepositoryAwareInterface;
use InvalidArgumentException;

/**
 * Base for View Providers.
 *
 * Implementors can define MATCHER_RELATIVE_NAMESPACE constant. If so, getMatcher() will return instances of objects relative
 * to this namespace if $matcherIdentifier argument doesn't begin with a '\' (FQ class name).
 */
abstract class Configured
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var array Matching configuration hash
     */
    protected $matchConfig;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\ViewProviderMatcher[]
     */
    protected $matchers;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param array $matchConfig
     */
    public function __construct( Repository $repository, array $matchConfig )
    {
        $this->repository = $repository;
        $this->matchConfig = $matchConfig;
        $this->matchers = array();
    }

    /**
     * Returns the matcher object.
     *
     * @param string $matcherIdentifier The matcher class.
     *                                  If it begins with a '\' it means it's a FQ class name, otherwise it is relative to
     *                                  static::MATCHER_RELATIVE_NAMESPACE namespace (if available).
     *
     * @throws \InvalidArgumentException
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher
     */
    protected function getMatcher( $matcherIdentifier )
    {
        // Caching the matcher instance in memory
        if ( isset( $this->matchers[$matcherIdentifier] ) )
        {
            return $this->matchers[$matcherIdentifier];
        }

        // Not a FQ class name, so take the relative namespace, if defined in descendant.
        if ( $matcherIdentifier[0] !== '\\' && defined( 'static::MATCHER_RELATIVE_NAMESPACE' ) )
        {
            $matcherIdentifier = static::MATCHER_RELATIVE_NAMESPACE . "\\$matcherIdentifier";
        }
        if ( !class_exists( $matcherIdentifier ) )
        {
            throw new InvalidArgumentException( "Invalid matcher class '$matcherIdentifier'" );
        }
        $this->matchers[$matcherIdentifier] = new $matcherIdentifier();

        if ( $this->matchers[$matcherIdentifier] instanceof RepositoryAwareInterface )
        {
            $this->matchers[$matcherIdentifier]->setRepository( $this->repository );
        }

        return $this->matchers[$matcherIdentifier];
    }

    /**
     * Does the matching between $matchConfig and $valueObject.
     * Returns a ContentView object if $valueObject has successfully matched a view or null if nothing has matched.
     *
     * @param array $matchConfig Hash containing all match configuration to check against $valueObject.
     *                           Must at least contain
     *                              - 'match' => hash with matcher identifier as key and matching rules as value.
     *                              - 'template' => Template identifier to use if match is successful.
     * @param \eZ\Publish\API\Repository\Values\ValueObject $valueObject
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|null
     */
    protected function doMatch( array $matchConfig, ValueObject $valueObject )
    {
        foreach ( $matchConfig as $configHash )
        {
            $hasMatched = true;
            foreach ( $configHash['match'] as $matcherIdentifier => $value )
            {
                /** @var $matcher \eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher */
                $matcher = $this->getMatcher( $matcherIdentifier );
                $matcher->setMatchingConfig( $value );
                if ( !$this->match( $matcher, $valueObject ) )
                    $hasMatched = false;
            }

            if ( $hasMatched )
            {
                return new ContentView( $configHash['template'] );
            }
        }
    }
}
