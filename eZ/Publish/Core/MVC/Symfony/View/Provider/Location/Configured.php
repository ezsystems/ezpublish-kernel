<?php
/**
 * File containing the View\Provider\Location\Configured class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider\Location;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Location as LocationViewProvider;
use eZ\Publish\Core\MVC\Symfony\View\Provider\Configured as ProviderConfigured;
use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher;
use eZ\Publish\Core\MVC\RepositoryAwareInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher;
use InvalidArgumentException;

class Configured extends ProviderConfigured implements LocationViewProvider
{
    /**
     * Returns a ContentView object corresponding to $location, or void if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content.
     *
     * @throws \InvalidArgumentException
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getView( Location $location, $viewType )
    {
        if ( !isset( $this->matchConfig[$viewType] ) )
            return;

        foreach ( $this->matchConfig[$viewType] as $configHash )
        {
            $hasMatched = true;
            foreach ( $configHash['match'] as $matcherIdentifier => $value )
            {
                // Caching the matcher instance in memory
                if ( !isset( $this->matchers[$matcherIdentifier] ) )
                {
                    $this->matchers[$matcherIdentifier] = $this->getMatcher( $matcherIdentifier );
                }
                $matcher = $this->matchers[$matcherIdentifier];

                if ( !$matcher instanceof Matcher )
                    throw new InvalidArgumentException(
                        'Matcher for ContentViewProvider\\Configured must implement eZ\\Publish\\MVC\\View\\ContentViewProvider\\Configured\\Matcher interface.'
                    );

                if ( $matcher instanceof RepositoryAwareInterface )
                    $matcher->setRepository( $this->repository );

                $matcher->setMatchingConfig( $value );
                if ( !$this->doMatch( $matcher, $location ) )
                    $hasMatched = false;
            }

            if ( $hasMatched )
            {
                return new ContentView( $configHash['template'] );
            }
        }
    }

    /**
     * Checks if $valueObject matches the $matcher's rules.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher $matcher
     * @param \eZ\Publish\API\Repository\Values\ValueObject $valueObject
     *
     * @throws \InvalidArgumentException If $valueObject is not of expected sub-type.
     *
     * @return bool
     */
    protected function doMatch( ViewProviderMatcher $matcher, ValueObject $valueObject )
    {
        if ( !$valueObject instanceof Location )
            throw new InvalidArgumentException( 'Value object must be a valid Location instance' );

        return $matcher->matchLocation( $valueObject );
    }
}
