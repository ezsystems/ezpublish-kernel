<?php
/**
 * File containing the Content CreateStruct struct
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 */
class CreateStruct extends ValueObject
{
    /**
     * @var string[] Eg. array( 'eng-GB' => "New Article" )
     */
    public $name;

    /**
     * @var int
     */
    public $typeId;

    /**
     * @var int
     */
    public $sectionId;

    /**
     * @var int
     */
    public $ownerId;

    /**
     * ContentId, contentVersion and mainLocationId are allowed to be left empty
     * when used on with this struct as these values are created by the create method.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct[]
     */
    public $locations = array();

    /**
     * Contains *all* fields of the object to be created.
     *
     * This attribute should contain *all* fields (in all language) of the
     * object to be created. If a field is not translatable, it may only occur
     * once. The storage layer will automatically take care that such fields
     * are assigned to each language version.
     *
     * @var Field[]
     */
    public $fields = array();

    /**
     * @var boolean Always available flag
     */
    public $alwaysAvailable = false;

    /**
     * @var string Remote identifier used as a custom identifier for the object
     */
    public $remoteId;

    /**
     * Language id the content was initially created in
     *
     * @var mixed
     */
    public $initialLanguageId;

    /**
     * Modification date
     *
     * @var int
     */
    public $modified;
}
