<?php
/**
 * File contains Field Collection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Field;
use ezp\Base\Collection\Lazy,
    ezp\Content\Service as ContentService,
    ezp\Content\Version;

/**
 * Field Collection class. Fields are indexed by field identifier
 * This collection uses lazy loading mechanism.
 */
class Collection extends Lazy
{
    /**
     * Constructor
     *
     * @param \ezp\Content\Service $contentService Content service to be used for fetching versions
     * @param \ezp\Content/Version $version Version this fields collection belongs to.
     */
    public function __construct( ContentService $contentService, Version $version )
    {
        parent::__construct( 'ezp\\Content\\Field', $contentService, $version, 'loadFields' );
    }
}
