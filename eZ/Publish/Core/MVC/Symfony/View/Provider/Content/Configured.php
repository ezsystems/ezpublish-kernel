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
use eZ\Publish\Core\MVC\Symfony\View\Provider\Configured as ProviderConfigured;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class Configured extends ProviderConfigured implements ContentViewProvider
{
    /**
     * Returns a ContentView object corresponding to $contentInfo, or void if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $viewType Variation of display for your content
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getView( ContentInfo $contentInfo, $viewType )
    {
    }
}
