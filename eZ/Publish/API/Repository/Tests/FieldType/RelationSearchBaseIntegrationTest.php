<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Tests\FieldType;

/**
 * Base integration test for field types handling content relations.
 *
 * @group integration
 * @group field-type
 * @group relation
 * @deprecated 6.1 use RelationSearchBaseIntegrationTestTrait instead.
 */
abstract class RelationSearchBaseIntegrationTest extends SearchBaseIntegrationTest
{
    use RelationSearchBaseIntegrationTestTrait;
}
