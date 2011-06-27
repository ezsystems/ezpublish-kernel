<?php
/**
 * Keyword Field domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage content
 */

/**
 * Keyword Field value object class
 */
namespace ezp\content\Field;
class Keyword extends String
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezkeyword';

    /**
     * @see \ezp\content\ContentFieldTypeInterface
     */
    public function __construct( \ezp\content\ContentFieldDefinitionInterface $contentTypeFieldType )
    {
        $this->types[] = self::FIELD_IDENTIFIER;
        parent::__construct( $contentTypeFieldType );
    }
}
