<?php
/**
 * File contains Version Collection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Version;
use ezp\Base\Collection\Lazy,
    ezp\Base\Service\Container as ServiceContainer,
    ezp\Content;

/**
 * Version Collection class. Versions are indexed by version number
 * This collection uses lazy loading mechanism.
 */
class Collection extends Lazy
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentType If elements contains item of wrong type
     * @param int|false $contentId Id of content this version collection belongs to.
     *                            false if it's for a new content
     */
    public function __construct( $contentId = false )
    {
        $sc = new ServiceContainer();
        parent::__construct( 'ezp\\Content\\Version', $sc->getRepository()->getContentService(), $contentId, 'listVersions' );
    }
}
