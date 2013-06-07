<?php
/**
 * File containing the View\Provider\Content\Configured class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider\Content;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Configured as BaseConfigured;
use eZ\Publish\Core\MVC\Symfony\View\Provider\Content as ContentViewProvider;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class Configured extends BaseConfigured implements ContentViewProvider
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
        $viewConfig = $this->matcherFactory->match( $viewType, $contentInfo );
        if ( empty( $viewConfig ) )
        {
            return;
        }

        return $this->buildContentView( $viewConfig );
    }
}
