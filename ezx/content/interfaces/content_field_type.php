<?php
/**
 * Interface for content field types
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */
namespace ezx\content;
interface ContentFieldTypeInterface
{
    /**
     * Called when content object is constructed
     *
     * This function can safely set default values, as values from db will be set afterwards if this is not a new object
     *
     * @param \ezx\content\Abstracts\FieldType $contentTypeFieldType
     */
    public function __construct( \ezx\content\Abstracts\FieldType $contentTypeFieldType );
}