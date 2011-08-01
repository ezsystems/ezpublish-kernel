<?php
/**
 * File containing the ContentType class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Type;
use ezp\Persistence\ValueObject;

/**
 *
 */
class CreateStruct extends ValueObject
{
    /**
     * Human readable name of the content type
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @var string[]
     */
    public $name;

    /**
     * Version (state) to create.
     *
     * @var int
     */
    public $version;

    /**
     * Human readable description of the content type
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @var string[]
     */
    public $description;

    /**
     * String identifier of a type
     *
     * @var string
     */
    public $identifier;

    /**
     * Creation date (timestamp)
     *
     * @var int
     */
    public $created;

    /**
     * Modification date (timestamp)
     *
     * @var int
     */
    public $modified;

    /**
     * Creator user id
     *
     * @var mixed
     */
    public $creatorId;

    /**
     * Modifier user id
     *
     * @var mixed
     *
     */
    public $modifierId;

    /**
     * Unique remote ID
     *
     * @var string
     */
    public $remoteId;

    /**
     * URL alias schema
     *
     * @var string
     */
    public $urlAliasSchema;

    /**
     * Name schema
     *
     * @var string
     */
    public $nameSchema;

    /**
     * Determines if the type is a container
     *
     * @var bool
     */
    public $isContainer;

    /**
     * Initial language
     *
     * @var mixed
     */
    public $initialLanguageId;

    /**
     * Contains an array of type group IDs
     *
     * @var mixed[]
     */
    public $contentTypeGroupIds = array();

    /**
     * Content fields in this type
     *
     * @var Type\FieldDefinition[]
     */
    public $fieldDefinitions = array();

    /**
     * Performs a deep cloning.
     *
     * @return void
     */
    public function __clone()
    {
        foreach ( $this->fieldDefinitions as $id => $fieldDef )
        {
            $this->fieldDefinitions[$id] = clone $fieldDef;
        }
    }
}
?>
