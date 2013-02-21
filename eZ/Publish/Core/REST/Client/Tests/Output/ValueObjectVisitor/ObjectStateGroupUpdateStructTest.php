<?php
/**
 * File containing a ObjectStateGroupUpdateStructTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\API\Repository\Values\ObjectState;

use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common;

class ObjectStateGroupUpdateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the ObjectStateGroupUpdateStruct visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getObjectStateGroupUpdateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $objectStateGroupUpdateStruct = new ObjectState\ObjectStateGroupUpdateStruct();
        $objectStateGroupUpdateStruct->identifier = 'some-group';
        $objectStateGroupUpdateStruct->defaultLanguageCode = 'eng-GB';
        $objectStateGroupUpdateStruct->names = array( 'eng-GB' => 'Some group EN', 'fre-FR' => 'Some group FR' );
        $objectStateGroupUpdateStruct->descriptions = array( 'eng-GB' => 'Description EN', 'fre-FR' => 'Description FR' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $objectStateGroupUpdateStruct
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the result contains names element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsNamesElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'names',
                'children' => array(
                    'count' => 2
                )
            ),
            $result,
            'Invalid <names> element.',
            false
        );
    }

    /**
     * Tests that the result contains descriptions element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDescriptionsElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'descriptions',
                'children' => array(
                    'count' => 2
                )
            ),
            $result,
            'Invalid <descriptions> element.',
            false
        );
    }

    /**
     * Tests that the result contains identifier value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'identifier',
                'content'  => 'some-group',
            ),
            $result,
            'Invalid or non-existing <ObjectStateGroupUpdate> identifier value element.',
            false
        );
    }

    /**
     * Tests that the result contains defaultLanguageCode value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDefaultLanguageCodeValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'defaultLanguageCode',
                'content'  => 'eng-GB',
            ),
            $result,
            'Invalid or non-existing <ObjectStateGroupUpdate> defaultLanguageCode value element.',
            false
        );
    }

    /**
     * Gets the ObjectStateGroupUpdateStruct visitor
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\ObjectStateGroupUpdateStruct
     */
    protected function getObjectStateGroupUpdateStructVisitor()
    {
        return new ValueObjectVisitor\ObjectStateGroupUpdateStruct(
            new Common\UrlHandler\eZPublish()
        );
    }
}
