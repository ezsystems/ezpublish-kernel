<?php
/**
 * Interface for content field init where it gets contentTypeFieldValue as value
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */
namespace ezx\doctrine\content;
interface Interface_Field_Init
{
    /**
     * Called when content object is created the first time
     *
     * @param \ezx\doctrine\Interface_Value $contentTypeFieldValue
     * @return Interface_Field_Init
     */
    public function init( \ezx\doctrine\Interface_Value $contentTypeFieldValue );
}