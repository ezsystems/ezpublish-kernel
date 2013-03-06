<?php
/**
 * File containing the Base class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page\Parts;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\FieldType\Page\PageService;

/**
 * @property-read \eZ\Publish\Core\FieldType\Page\PageService $pageService Service dedicated to Page fieldtype, containing block and zone definition.
 */
class Base extends ValueObject
{
    const ACTION_ADD = 'add';

    const ACTION_MODIFY = 'modify';

    const ACTION_REMOVE = 'remove';

    /**
     * @var \eZ\Publish\Core\FieldType\Page\PageService
     */
    protected $pageService;

    /**
     * Hash of arbitrary attributes.
     *
     * @var array
     */
    public $attributes;

    /**
     * Constructor
     *
     * @param \eZ\Publish\Core\FieldType\Page\PageService $pageService
     * @param array $properties
     */
    public function __construct( PageService $pageService, array $properties = array() )
    {
        $this->pageService = $pageService;
        $this->attributes = array();
        parent::__construct( $properties );
    }

    /**
     * Returns available properties with their values as a simple hash.
     *
     * @return array
     */
    public function getState()
    {
        $hash = array();

        foreach ( $this->getProperties() as $property )
        {
            if ( $property === 'pageService' )
                continue;

            $hash[$property] = $this->$property;
        }

        return $hash;
    }
}
