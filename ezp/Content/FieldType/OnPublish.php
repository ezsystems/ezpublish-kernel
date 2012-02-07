<?php
/**
 * File containing the \ezp\Content\FieldType\OnContentPublish interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType;

use ezp\Base\Repository,
    ezp\Content\Field;

/**
 * This interface is used to add on publish events handling to a FieldType
 *
 * <code>
 * <?php
 * use ezp\Content\FieldType\OnPublish;
 *
 * class MyField extends FieldType implements OnPublish
 * {
 * }
 * ?>
 * </code>
 */

interface OnPublish
{
    /**
     * Event handler for pre_publish, triggered by the version
     *
     * @param \ezp\Base\Repository $repository The repository instance
     * @param \ezp\Content\Field The Field being published
     */
    function onPrePublish( Repository $repository, Field $field );

    /**
     * Event handler for post_publish, triggered by the version
     *
     * @param \ezp\Base\Repository $repository The repository instance
     * @param \ezp\Content\Field The Field being published
     */
    function onPostPublish( Repository $repository, Field $field );
}
