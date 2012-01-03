<?php
/**
 * File containing the content updater action class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater;
use ezp\Persistence\Content,
    ezp\Persistence\Storage\Legacy\Content\Gatway as ContentGateway;

/**
 * Updater action base class
 */
abstract class Action
{
    /**
     * Content gateway
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * Creates a new action
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Gateway $contentGateway
     */
    public function __construct( ContentGateway $contentGateway )
    {
        $this->contentGateway = $contentGateway;
    }

    /**
     * Applies the action to the given $content
     *
     * @param Content $content
     * @return void
     */
    abstract public function apply( Content $content );
}
