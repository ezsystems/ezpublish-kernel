<?php

/**
 * File containing a ContentObjectStatesTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;

class ContentObjectStates extends BaseTest
{
    /**
     * Tests the parsing of ContentObjectStates.
     */
    public function testParse()
    {
        $contentStatesParser = $this->getParser();

        $inputArray = array(
            'ObjectState' => array(
                array('_media-type' => 'application/vnd.ez.api.ObjectState+xml'),
                array('_media-type' => 'application/vnd.ez.api.ObjectState+xml'),
            ),
        );

        $this->getParsingDispatcherMock()
            ->expects($this->exactly(2))
            ->method('parse')
            ->with(
                array('_media-type' => 'application/vnd.ez.api.ObjectState+xml'),
                'application/vnd.ez.api.ObjectState+xml'
            )
            ->will($this->returnValue('foo'));

        $result = $contentStatesParser->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertEquals(
            array('foo', 'foo'),
            $result
        );
    }

    /**
     * Gets the ContentObjectStates parser.
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\ContentObjectStates;
     */
    protected function getParser()
    {
        return new Parser\ContentObjectStates();
    }
}
