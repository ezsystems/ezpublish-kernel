<?php
/**
 * File containing the \ezp\Content\FieldType\OnContentPublish interface.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType;

use ezp\Base\Repository,
    ezp\Content\Field;

/**
 * This interface is used to add onContentPublish event handling to a FieldType
 *
 * <code>
 * <?php
 * use ezp\Content\FieldType\OnContentPublish;
 *
 * class MyField extends FieldType implements OnContentPublish
 * {
 * }
 * ?>
 * </code>
 */

interface OnContentPublish
{
    /**
     * Event handler for content/publish
     * @param \ezp\Base\Repository $repository The repository instance
     * @param \ezp\Content\Field The Field being published
     */
    function onContentPublish( Repository $repository, Field $field );
}
?>
