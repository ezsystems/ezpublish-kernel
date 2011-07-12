<?php
/**
 * File containing the ContentType class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage persistence_content_type
 */

namespace ezp\Persistence\Content;

/**
 * @package ezp
 * @subpackage persistence_content
 */
class Type
{
    /**
     * Primary key
     *
     * @var mixed
     */
    public $id;

    /**
     * Name
     *
     * @var string[]
     */
    public $name;

    /**
     * Description
     *
     * @var string[]
     */
    public $description;

    /**
     * Readable string identifier of a type
     *
     * @var string
     */
    public $identifier;

    /**
     * Created date (timestamp)
     *
     * @var int
     */
    public $created;

    /**
     * Modified date (timestamp)
     *
     * @var int
     */
    public $modified;

    /**
     * Creator user id
     *
     * @var mixed
     */
    public $creator;

    /**
     * Modifier user id
     *
     * @var mixed
     *
     */
    public $modifier;

    /**
     */
    public $remoteId;

    /**
     */
    public $urlAliasSchema;

    /**
     */
    public $nameSchema;

    /**
     */
    public $container;

    /**
     */
    public $initialLanguage;

    /**
     * Contains an array of type group IDs
     *
     * @var mixed[]
     */
    public $contentTypeGroupIDs = array();

    /**
     * @var Type\FieldDefinition[]
     */
    public $fieldDefinitions = array();
}
?>
