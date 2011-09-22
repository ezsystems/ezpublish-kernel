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
    ezp\Base\Observable,
    ezp\Base\Observer,
    ezp\Base\Exception\PropertyNotFound,
    ezp\Persistence\Repository\Handler,
    ezp\Persistence\ValueObject;

/**
 * Abstract Repository Services
 *
 */
abstract class Service implements Observable
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
     * List of event listeners
     *
     * @var \ezp\Base\Observer[]
     */
    private $observers = array();

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
     * @param array $optionalProperties List of properties that is optional
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing, has a value of null
     *                                              and {@link setPropertyByConvention()} returns false.
     * @uses setPropertyByConvention()
     */
    protected function fillStruct( ValueObject $struct, Model $do, array $optionalProperties = array() )
    {
        $vo = $do->getState( 'properties' );
        foreach ( $struct as $property => $value )
        {
            // set property value if there is one
            if ( isset( $vo->$property ) )
            {
                $struct->$property = $vo->$property;
                continue;
            }
            // Struct contains default value
            else if ( $struct->$property !== null )
            {
                continue;
            }
            // Try to set by convention, if not throw PropertyNotFound exception
            else if ( $this->setPropertyByConvention( $struct, $property ) )
            {
                continue;
            }
            // continue if property was optional
            else if ( in_array( $property, $optionalProperties ) )
            {
                continue;
            }
            throw new PropertyNotFound( $property, get_class( $do ) );
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
            case 'published':
                $struct->$property = time();
                break;
            case 'creatorId':
            case 'modifierId':
                $struct->$property = $this->repository->getUser()->id;
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * Attach the event listener $observer for $event to the service
     *
     * @param \ezp\Base\Observer $observer
     * @param string $event
     * @return \ezp\Base\Service
     */
    public function attach( Observer $observer, $event = 'update' )
    {
        if ( isset( $this->observers[$event] ) )
        {
            $this->observers[$event][] = $observer;
        }
        else
        {
            $this->observers[$event] = array( $observer );
        }
        return $this;
    }

    /**
     * Detach $observer for $event from the service
     *
     * @param \ezp\Base\Observer $observer
     * @param string $event
     * @return \ezp\Base\Service
     */
    public function detach( Observer $observer, $event = 'update' )
    {
        if ( !empty( $this->observers[$event] ) )
        {
            foreach ( $this->observers[$event] as $key => $obj )
            {
                if ( $obj === $observer )
                    unset( $this->observers[$event][$key] );
            }
        }
        return $this;
    }

    /**
     * Notify registered observers about $event
     *
     * @param string $event
     * @param array|null $arguments
     * @return \ezp\Base\Service
     */
    public function notify( $event = 'update', array $arguments = null )
    {
        if ( !empty( $this->observers[$event] ) )
        {
            foreach ( $this->observers[$event] as $obj )
            {
                $obj->update( $this, $event, $arguments );
            }
        }
        return $this;
    }
}

