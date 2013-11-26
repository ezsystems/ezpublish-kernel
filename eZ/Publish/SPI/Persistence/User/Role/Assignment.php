<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\User\Role\Assignment class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence\User\Role;


use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * The base class for role assignments.
 */
abstract class Assignment extends ValueObject
{
    /**
     * the unique id of the role assignment
     *
     * @var mixed
     */
    public $id;

    /**
     * The Role connected to this assignment
     *
     * @var mixed
     */
    public $roleId;

    /**
     * One of 'Subtree' or 'Section'
     *
     * @var string|null
     */
    public $limitationIdentifier;

    /**
     * The subtree paths or section ids.
     *
     * @var mixed[]|null
     */
    public $values;

}
