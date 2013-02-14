<?php
/**
 * File containing the content updater action class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;

/**
 * Updater action base class
 */
abstract class Action
{
    /**
     * Content gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * Creates a new action
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     */
    public function __construct( ContentGateway $contentGateway )
    {
        $this->contentGateway = $contentGateway;
    }

    /**
     * Applies the action to the given $content
     *
     * @param Content $content
     *
     * @return void
     */
    abstract public function apply( Content $content );
}
