<?php
/**
 * File containing the View\Provider\Content interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;

/**
 * Interface for content view providers.
 *
 * Content view providers select a view for a given content, depending on its own internal rules.
 */
interface Content
{
    /**
     * Returns a ContentView object corresponding to $contentInfo, or void if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $viewType Variation of display for your content
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getView( ContentInfo $contentInfo, $viewType );
}
