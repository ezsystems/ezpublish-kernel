<?php
/**
 * File containing the EzcDatabase Composite field value handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\Handler;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\Handler;

/**
 * Content locator gateway implementation using the zeta database component.
 *
 * Composite value handler is used for creating a filter on a value that can be partially matched.
 * Eg. TextLine string, where it makes sense to match only a part of the sentence.
 */
class Composite extends Handler
{
}
