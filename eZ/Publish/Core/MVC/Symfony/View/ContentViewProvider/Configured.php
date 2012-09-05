<?php
/**
 * File containing the Configured class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider,
    eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher,
    eZ\Publish\Core\MVC\RepositoryAwareInterface,
    eZ\Publish\API\Repository\Repository,
    eZ\Publish\API\Repository\Values\Content\ContentInfo,
    eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\Core\MVC\Symfony\View\ContentView;

class Configured implements ContentViewProvider
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var array Matching configuration hash for location only
     */
    protected $locationMatchConfig;

    /**
     * @var array Matching configuration hash for content only
     */
    protected $contentMatchConfig;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher[]
     */
    protected $matchers;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param array $locationMatchConfig
     * @param array $contentMatchConfig
     */
    public function __construct( Repository $repository, array $locationMatchConfig, array $contentMatchConfig )
    {
        $this->repository = $repository;
        $this->locationMatchConfig = $locationMatchConfig;
        $this->contentMatchConfig = $contentMatchConfig;
        $this->matchers = array();
    }

    /**
     * Returns a ContentView object corresponding to $contentInfo, or void if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $viewType Variation of display for your content
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getViewForContent( ContentInfo $contentInfo, $viewType )
    {
    }

    /**
     * Returns a ContentView object corresponding to $location, or void if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content.
     * @throws \InvalidArgumentException
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getViewForLocation( Location $location, $viewType )
    {
        foreach ( $this->locationMatchConfig as $configHash )
        {
            if ( $configHash['viewType'] === $viewType )
            {
                $hasMatched = false;
                foreach ( $configHash['match'] as $matcherIdentifier => $value )
                {
                    // Caching the matcher instance in memory
                    if ( !isset( $this->matchers[$matcherIdentifier] ) )
                    {
                        $this->matchers[$matcherIdentifier] = $this->getMatcher( $matcherIdentifier );
                    }
                    $matcher = $this->matchers[$matcherIdentifier];

                    if ( !$matcher instanceof Matcher )
                        throw new \InvalidArgumentException(
                            'Matcher for ContentViewProvider\\Configured must implement eZ\\Publish\\MVC\\View\\ContentViewProvider\\Configured\\Matcher interface.'
                        );

                    if ( $matcher instanceof RepositoryAwareInterface )
                        $matcher->setRepository( $this->repository );

                    $matcher->setMatchingConfig( $value );
                    $hasMatched = $matcher->matchLocation( $location );
                }

                if ( $hasMatched )
                {
                    return new ContentView( $configHash['matchTemplate'] );
                }
            }
        }
    }

    /**
     * Returns the matcher object.
     *
     * @param string $matcherIdentifier The matcher class. If it begins with a '\' it means it's a FQ class name, otherwise it is relative to this namespace.
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher
     */
    protected function getMatcher( $matcherIdentifier )
    {
        if ( $matcherIdentifier[0] !== '\\' )
            $matcherIdentifier = __NAMESPACE__ . "\\Configured\\Matcher\\$matcherIdentifier";

        return new $matcherIdentifier();
    }
}
