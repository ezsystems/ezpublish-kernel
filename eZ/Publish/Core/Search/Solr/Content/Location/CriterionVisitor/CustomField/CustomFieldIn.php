<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Location\CriterionVisitor\CustomField;

use eZ\Publish\Core\Search\Solr\Content\CriterionVisitor\CustomField\CustomFieldIn as ContentCustomFieldIn;

/**
 * Visits the CustomField criterion with IN, EQ or CONTAINS operator.
 */
class CustomFieldIn extends ContentCustomFieldIn
{
}
