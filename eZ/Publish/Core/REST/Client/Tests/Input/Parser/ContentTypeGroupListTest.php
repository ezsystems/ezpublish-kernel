<?php

/**
 * File containing a ContentTypeGroupListTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;

class ContentTypeGroupListTest extends BaseTest
{
    /**
     * Tests the parsing of ContentTypeGroupList.
     */
    public function testParse()
    {
        $groupListParser = $this->getParser();

        $inputArray = array(
            'ContentTypeGroup' => array(
                array('_media-type' => 'application/vnd.ez.api.ContentTypeGroup+xml'),
                array('_media-type' => 'application/vnd.ez.api.ContentTypeGroup+xml'),
            ),
        );

        $this->getParsingDispatcherMock()
            ->expects($this->exactly(2))
            ->method('parse')
            ->with(
                array('_media-type' => 'application/vnd.ez.api.ContentTypeGroup+xml'),
                'application/vnd.ez.api.ContentTypeGroup+xml'
            )
            ->will($this->returnValue('foo'));

        $result = $groupListParser->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertEquals(
            array('foo', 'foo'),
            $result
        );
    }

    /**
     * Gets the ContentTypeGroupList parser.
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\ContentTypeGroupList;
     */
    protected function getParser()
    {
        return new Parser\ContentTypeGroupList();
    }
}
