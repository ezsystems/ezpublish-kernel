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
    ezp\Content\Service as ContentService;

/**
 * Version Collection class. Versions are indexed by version number
 * This collection uses lazy loading mechanism.
 */
class Collection extends Lazy
{
    /**
     * Constructor
     *
     * @param \ezp\Content\Service $contentService Content service to be used for fetching versions
     * @param int $contentId Id of content this version collection belongs to.
     */
    public function __construct( ContentService $contentService, $contentId )
    {
        parent::__construct( 'ezp\\Content\\Version', $contentService, $contentId, 'listVersions' );
    }
}
