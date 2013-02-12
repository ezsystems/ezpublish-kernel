<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Values\FieldDefinitionList;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server;
use eZ\Publish\Core\REST\Common;

use eZ\Publish\Core\Repository\Values;

class FieldDefinitionListTest extends ValueObjectVisitorBaseTest
{
    /**
     * @return \DOMDocument
     */
    public function testVisitFieldDefinitionList()
    {
        $visitor   = $this->getContentVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $restContent = $this->getBasicFieldDefinitionList();

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\Core\\REST\\Server\\Values\\RestFieldDefinition' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $restContent
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        $dom = new \DOMDocument();
        $dom->loadXml( $result );

        return $dom;
    }

    protected function getBasicFieldDefinitionList()
    {
        return new Server\Values\FieldDefinitionList(
            new Values\ContentType\ContentType(
                array(
                    'id' => 'contentTypeId',
                    'status' => Values\ContentType\ContentType::STATUS_DEFINED,
                    'fieldDefinitions' => array(),
                )
            ),
            array(
                new Values\ContentType\FieldDefinition(
                    array( 'id' => 'fieldDefinitionId_1' )
                ),
                new Values\ContentType\FieldDefinition(
                    array( 'id' => 'fieldDefinitionId_2' )
                )
            )
        );
    }

    public function provideXpathAssertions()
    {
        return array(
            array(
                '/FieldDefinitions[@href="/content/types/contentTypeId/fieldDefinitions"]'
            ),
            array(
                '/FieldDefinitions[@media-type="application/vnd.ez.api.FieldDefinitionList+xml"]'
            ),
        );
    }

    /**
     * @param string $xpath
     * @param \DOMDocument $dom
     *
     * @depends testVisitFieldDefinitionList
     * @dataProvider provideXpathAssertions
     */
    public function testGeneratedXml( $xpath, \DOMDocument $dom )
    {
        $this->assertXPath( $dom, $xpath );
    }

    /**
     * Get the Content visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\FieldDefinitionList
     */
    protected function getContentVisitor()
    {
        return new ValueObjectVisitor\FieldDefinitionList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
