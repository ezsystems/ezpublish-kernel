<?php

/**
 * File containing ValueObjectVisitorBaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output;

use eZ\Publish\Core\REST\Common\Tests\AssertXmlTagTrait;
use eZ\Publish\Core\REST\Common\RequestParser\EzPublish as EzPublishRequestParser;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest as CommonValueObjectVisitorBaseTest;

abstract class ValueObjectVisitorBaseTest extends CommonValueObjectVisitorBaseTest
{
    use AssertXmlTagTrait;

    /** @var \eZ\Publish\Core\REST\Common\RequestParser\EzPublish */
    protected $requestParser;

    /**
     * @return \eZ\Publish\Core\REST\Common\RequestParser\EzPublish
     */
    protected function getRequestParser()
    {
        if (!isset($this->requestParser)) {
            $this->requestParser = new EzPublishRequestParser();
        }

        return $this->requestParser;
    }
}
