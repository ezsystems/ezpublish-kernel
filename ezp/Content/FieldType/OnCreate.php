<?php
/**
 * File containing the \ezp\Content\FieldType\OnCreate interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType;

use ezp\Base\Repository,
    ezp\Content\Field;

/**
 * This interface is used to add on create events handling to a FieldType
 *
 * <code>
 * <?php
 * use ezp\Content\FieldType\OnCreate;
 *
 * class MyField extends FieldType implements OnCreate
 * {
 * }
 * ?>
 * </code>
 */

interface OnCreate
{
    /**
     * Event handler for pre_create, triggered by the version
     *
     * @param \ezp\Base\Repository $repository The repository instance
     * @param \ezp\Content\Field The Field being created
     */
    function onPreCreate( Repository $repository, Field $field );

    /**
     * Event handler for post_create, triggered by the version
     *
     * @param \ezp\Base\Repository $repository The repository instance
     * @param \ezp\Content\Field The Field being created
     */
    function onPostCreate( Repository $repository, Field $field );
}
?>
