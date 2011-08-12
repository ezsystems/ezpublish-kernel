<?php
/**
 * File contains abstract Service, for services attached to repository
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezp\Base\Repository,
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
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing on $do or has a value of null
     *                                              Unless one of these properties which is filled by conventions:
     *                                              - remoteId
     *                                              - created
     *                                              - modified
     *                                              - creatorId
     *                                              - modifierId
     */
    protected function fillStruct( ValueObject $struct, Model $do )
    {
        $state = $do->getState();
        $vo = $state['properties'];
        foreach ( $struct as $property => $value )
        {
            // set property value if there is one
            if ( isset( $vo->$property ) )
            {
                $struct->$property = $vo->$property;
                continue;
            }

            // set by convention if match, if not throw PropertyNotFound exception
            switch ( $property )
            {
                case 'remoteId':
                    $struct->$property = md5( uniqid( get_class( $do ), true ) );
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
                    throw new PropertyNotFound( $property, get_class( $do ) );
            }
        }
    }
}
