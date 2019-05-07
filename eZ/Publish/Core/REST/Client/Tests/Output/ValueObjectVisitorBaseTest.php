<?php

/**
 * File containing ValueObjectVisitorBaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output;

use EzSystems\EzPlatformRestCommon\Tests\AssertXmlTagTrait;
use EzSystems\EzPlatformRestCommon\RequestParser\EzPublish as EzPublishRequestParser;
use EzSystems\EzPlatformRestCommon\Tests\Output\ValueObjectVisitorBaseTest as CommonValueObjectVisitorBaseTest;

abstract class ValueObjectVisitorBaseTest extends CommonValueObjectVisitorBaseTest
{
    use AssertXmlTagTrait;

    /**
     * @var \EzSystems\EzPlatformRestCommon\RequestParser\EzPublish
     */
    protected $requestParser;

    /**
     * @return \EzSystems\EzPlatformRestCommon\RequestParser\EzPublish
     */
    protected function getRequestParser()
    {
        if (!isset($this->requestParser)) {
            $this->requestParser = new EzPublishRequestParser();
        }

        return $this->requestParser;
    }
}
