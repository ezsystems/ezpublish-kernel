<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\QueryType;

use eZ\Publish\Core\MVC\Symfony\View\ContentView;

/**
 * Maps a ContentView to a QueryType.
 */
interface ContentViewQueryTypeMapper
{
    /**
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentView $contentView
     * @return \eZ\Publish\Core\QueryType\QueryType
     */
    public function map(ContentView $contentView);
}
