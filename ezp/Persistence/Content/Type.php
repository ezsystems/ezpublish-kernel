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
class Type extends ContentTypeBase
{
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
     * @var Type\Group[]
     */
    public $contentTypeGroups = array();

    /**
     * @var Type\FieldDefinition[]
     */
    public $fieldDefinition = array();
}
?>
