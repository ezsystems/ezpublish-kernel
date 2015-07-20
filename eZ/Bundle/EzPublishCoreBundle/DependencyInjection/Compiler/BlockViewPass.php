<?php

/**
 * File containing the BlockViewPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

/**
 * The BlockViewPass adds DIC compiler pass related to block view.
 * This includes adding BlockViewProvider implementations.
 *
 * @see \eZ\Publish\Core\MVC\Symfony\View\Manager
 */
class BlockViewPass extends ViewPass
{
    const VIEW_PROVIDER_IDENTIFIER = 'ezpublish.block_view_provider';
    const ADD_VIEW_PROVIDER_METHOD = 'addBlockViewProvider';
}
