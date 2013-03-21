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

abstract class Base extends ValueObject
{
    const ACTION_ADD = 'add';

    const ACTION_MODIFY = 'modify';

    const ACTION_REMOVE = 'remove';

    /**
     * Hash of arbitrary attributes.
     *
     * @var array
     */
    public $attributes;

    /**
     * Constructor
     *
     * @param array $properties
     */
    public function __construct( array $properties = array() )
    {
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
            $hash[$property] = $this->$property;
        }

        return $hash;
    }
}
