<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Values\ContentObjectStates;

class ContentObjectStatesTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentObjectStates visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $stateList = new ContentObjectStates(array());

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $stateList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains ContentObjectStates element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentObjectStatesElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'ContentObjectStates',
            ),
            $result,
            'Invalid <ContentObjectStates> element.',
            false
        );
    }

    /**
     * Test if result contains ContentObjectStates element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentObjectStatesAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'ContentObjectStates',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ContentObjectStates+xml',
                ),
            ),
            $result,
            'Invalid <ContentObjectStates> attributes.',
            false
        );
    }

    /**
     * Get the ContentObjectStates visitor.
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\ContentObjectStates
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\ContentObjectStates();
    }
}
