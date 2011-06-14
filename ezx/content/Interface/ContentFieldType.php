<?php
/**
 * Interface for content field types
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */
namespace ezx\content;
interface Interface_ContentFieldType
{
    /**
     * Called when content object is constructed
     *
     * This function can safely set default values, as values from db will be set afterwards if this is not a new object
     *
     * @param Abstract_FieldType $contentTypeFieldType
     */
    public function __construct( Abstract_FieldType $contentTypeFieldType );
}