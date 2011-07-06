<?php
/**
 * File contains Field Collection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage content
 */

/**
 * Field Collection class
 *
 * Readonly class that takes (Content) Version as input.
 *
 * @package ezp
 * @subpackage content
 */
namespace ezp\content;
class FieldCollection extends \ezp\base\ReadOnlyCollection
{
    /**
     * Constructor, sets up FieldCollection based on contentType fields
     *
     * @param Version $contentVersion
     */
    public function __construct( Version $contentVersion )
    {
        $elements = array();
        foreach ( $contentVersion->content->contentType->fields as $contentTypeField )
        {
            $elements[ $contentTypeField->identifier ] = $field = new Field( $contentVersion, $contentTypeField );
            $contentVersion->attach( $field, 'store' );
        }
        parent::__construct( $elements );
    }
}

?>
