<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\ContentType\Validator class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ValueObject,
    eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException,
    eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;

/**
 * This class represents a validator provided by a field type.
 * It consists of a identifier and a set of constraints.
 * The field type implementations are providing a set of concrete validators.
 *
 * @property-read string $identifier The unique identifier of the validator
 */
abstract class Validator extends ValueObject
{
    public function __construct( array $properties = array() )
    {
        if ( isset( $properties["identifier"] ) )
        {
            throw new PropertyReadOnlyException( "identifier", get_class( $this ) );
        }

        parent::__construct( $properties );
    }

    /**
     * Magic getter.
     * Returns constraint value, from its $name
     *
     * @param string $name
     * @return mixed
     * @throws \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function __get( $name )
    {
        if ( $name === "identifier" )
            return $this->identifier;

        if ( !isset( $this->constraints[$name] ) )
            return false;

        return $this->constraints[$name];
    }

    /**
     * Magic setter.
     * Sets $value to constraint, identified by $name
     *
     * @param string $name
     * @param mixed $value
     * @throws \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function __set( $name, $value )
    {
        $this->constraints[$name] = $value;
    }

    /**
     * A map of the parameters of the validator
     *
     * @var array
     */
    public $constraints;
}
