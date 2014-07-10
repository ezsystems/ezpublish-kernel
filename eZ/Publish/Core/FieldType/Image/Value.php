<?php
    /**
 * File containing the Image Value class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;

/**
 * Repository value for Image field type, as returned when loading content.
 *
 * @property string $path Used for BC with 5.0 (EZP-20948). Equivalent to $id.
 */
class Value extends BaseValue
{
    /**
     * Image id
     * @var mixed
     */
    public $id;

    /**
     * The image's HTTP URI
     * @var string
     */
    public $uri;

    /**
     * External image ID (required by REST for now, see https://jira.ez.no/browse/EZP-20831)
     * @var mixed
     */
    public $imageId;

    /**
     * Construct a new Value object.
     *
     * @param array $imageData Associatinve array of properties
     *
     * @throws PropertyNotFoundException if the given
     */
    public function __construct( array $imageData = array() )
    {
        // BC with 5.0 (EZP-20948)
        if ( isset( $imageData['path'] ) )
        {
            $imageData['id'] = $imageData['path'];
            unset( $imageData['path'] );
        }

        foreach ( $imageData as $key => $value )
        {
            try
            {
                $this->$key = $value;
            }
            catch ( PropertyNotFoundException $e )
            {
                throw new InvalidArgumentType(
                    sprintf( '$imageData[%s]', $key ),
                    'Property not found',
                    $value
                );
            }
        }
    }

    public function __get( $propertyName )
    {
        if ( $propertyName == 'path' )
            return $this->id;

        throw new PropertyNotFoundException( $propertyName, get_class( $this ) );
    }

    public function __set( $propertyName, $propertyValue )
    {
        if ( $propertyName == 'path' )
            $this->id = $propertyValue;

        throw new PropertyNotFoundException( $propertyName, get_class( $this ) );
    }
}
