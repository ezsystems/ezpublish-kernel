<?php
/**
 * File contains abstract Service, for services attached to repository
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezp\Base\Model,
    ezp\Base\Repository,
    ezp\Base\Exception\PropertyNotFound,
    ezp\Persistence\Repository\Handler,
    ezp\Persistence\ValueObject;

/**
 * Abstract Repository Services
 *
 */
abstract class Service
{
    /**
     * @var \ezp\Base\Repository
     */
    protected $repository;

    /**
     * @var \ezp\Persistence\Repository\Handler
     */
    protected $handler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \ezp\Base\Repository $repository
     * @param \ezp\Persistence\Repository\Handler $handler
     */
    public function __construct( Repository $repository, Handler $handler )
    {
        $this->repository = $repository;
        $this->handler = $handler;
    }

    /**
     * General method to fill in property values in struct from model->property values
     *
     * @param \ezp\Persistence\ValueObject $struct
     * @param \ezp\Base\Model $do
     * @param array $skipProperties List of properties that should be skipped
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing, has a value of null
     *                                              and {@link setPropertyByConvention()} returns false.
     * @uses setPropertyByConvention()
     */
    protected function fillStruct( ValueObject $struct, Model $do, array $skipProperties = array() )
    {
        $vo = $do->getState( 'properties' );
        foreach ( $struct as $property => $value )
        {
            // skip property if mentioned in $skipProperties
            if ( in_array( $property, $skipProperties ) )
            {
                continue;
            }
            // set property value if there is one
            else if ( isset( $vo->$property ) )
            {
                $struct->$property = $vo->$property;
                continue;
            }
            else if ( $struct->$property !== null )
            {
                continue;// Struct contains default value
            }

            // Try to set by convention, if not throw PropertyNotFound exception
            if ( !$this->setPropertyByConvention( $struct, $property ) )
            {
                throw new PropertyNotFound( $property, get_class( $do ) );
            }
        }
        return $struct;
    }

    /**
     * General method to fill in property value by convention
     *
     * Properties filled by convention:
     *     - remoteId
     *     - created
     *     - modified
     *     - creatorId
     *     - modifierId
     *
     * @param \ezp\Persistence\ValueObject $struct
     * @param string $property
     * @return bool False if no property was set by convention
     */
    protected function setPropertyByConvention( ValueObject $struct, $property )
    {
        switch ( $property )
        {
            case 'remoteId':
                $struct->$property = md5( uniqid( get_class( $struct ), true ) );
                break;
            case 'created':
            case 'modified':
                $struct->$property = time();
                break;
            case 'creatorId':
            case 'modifierId':
                $struct->$property = 14;// @todo Use user object when that is made part of repository/services
                break;
            default:
                return false;
        }
        return true;
    }
}
