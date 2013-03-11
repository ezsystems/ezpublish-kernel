<?php
/**
 * File containing the View\Provider\Content\Configured class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider\Content;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Content as ContentViewProvider;
use eZ\Publish\Core\MVC\Symfony\View\Provider\ContentBasedConfigured as ProviderConfigured;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher;
use eZ\Publish\API\Repository\Values\ValueObject;
use InvalidArgumentException;

class Configured extends ProviderConfigured implements ContentViewProvider
{
    /**
     * Returns a ContentView object corresponding to $contentInfo, or null if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $viewType Variation of display for your content
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|null
     */
    public function getView( ContentInfo $contentInfo, $viewType )
    {
        if ( !isset( $this->matchConfig[$viewType] ) )
            return;

        return $this->doMatch( $this->matchConfig[$viewType], $contentInfo );
    }

    /**
     * {@inheritDoc}
     */
    public function match( ViewProviderMatcher $matcher, ValueObject $valueObject )
    {
        if ( !$valueObject instanceof ContentInfo )
            throw new InvalidArgumentException( 'Value object must be a valid ContentInfo instance' );

        return $matcher->matchContentInfo( $valueObject );
    }
}
