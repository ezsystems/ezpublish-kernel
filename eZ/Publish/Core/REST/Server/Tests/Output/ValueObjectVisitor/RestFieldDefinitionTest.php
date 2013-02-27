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
use eZ\Publish\Core\REST\Server\Values\RestFieldDefinition;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server;
use eZ\Publish\Core\REST\Common;

use eZ\Publish\Core\Repository\Values;

class RestFieldDefinitionTest extends ValueObjectVisitorBaseTest
{
    protected $fieldTypeSerializerMock;

    public function setUp()
    {
        $this->fieldTypeSerializerMock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Common\\Output\\FieldTypeSerializer',
            array(),
            array(),
            '',
            false
        );
    }

    /**
     * @return \DOMDocument
     */
    public function testVisitRestFieldDefinition()
    {
        $visitor   = $this->getContentVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $restFieldDefinition = $this->getBasicRestFieldDefinition();

        $this->fieldTypeSerializerMock->expects( $this->once() )
            ->method( 'serializeFieldDefaultValue' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\Core\\REST\\Common\\Output\\Generator' ),
                $this->equalTo( 'my-field-type' ),
                $this->equalTo(
                    'my default value text'
                )
            );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $restFieldDefinition
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        $dom = new \DOMDocument();
        $dom->loadXml( $result );

        return $dom;
    }

    protected function getBasicRestFieldDefinition()
    {
        return new Server\Values\RestFieldDefinition(
            new Values\ContentType\ContentType(
                array(
                    'id' => 'contentTypeId',
                    'status' => Values\ContentType\ContentType::STATUS_DEFINED,
                    'fieldDefinitions' => array(),
                )
            ),
            new Values\ContentType\FieldDefinition(
                array(
                    'id' => 'fieldDefinitionId_23',
                    'fieldSettings' => array( 'setting' => 'foo' ),
                    'validatorConfiguration' => array( 'validator' => 'bar' ),
                    'identifier' => 'title',
                    'fieldGroup' => 'abstract-field-group',
                    'position' => 2,
                    'fieldTypeIdentifier' => 'my-field-type',
                    'isTranslatable' => true,
                    'isRequired' => false,
                    'isSearchable' => true,
                    'isInfoCollector' => false,
                    'defaultValue' => 'my default value text',
                    'names' => array( 'eng-US' => 'Sindelfingen' ),
                    'descriptions' => array( 'eng-GB' => 'Bielefeld' ),
                )
            )
        );
    }

    public function provideXpathAssertions()
    {
        $xpathAssertions = array(
            '/FieldDefinition[@href="/content/types/contentTypeId/fieldDefinitions/fieldDefinitionId_23"]',
            '/FieldDefinition[@media-type="application/vnd.ez.api.FieldDefinition+xml"]',
            '/FieldDefinition/id[text()="fieldDefinitionId_23"]',
            '/FieldDefinition/identifier[text()="title"]',
            '/FieldDefinition/fieldType[text()="my-field-type"]',
            '/FieldDefinition/fieldGroup[text()="abstract-field-group"]',
            '/FieldDefinition/position[text()="2"]',
            '/FieldDefinition/isTranslatable[text()="true"]',
            '/FieldDefinition/isRequired[text()="false"]',
            '/FieldDefinition/isInfoCollector[text()="false"]',
            '/FieldDefinition/isSearchable[text()="true"]',
            '/FieldDefinition/names',
            '/FieldDefinition/names/value[@languageCode="eng-US" and text()="Sindelfingen"]',
            '/FieldDefinition/descriptions/value[@languageCode="eng-GB" and text()="Bielefeld"]',
        );

        return array_map(
            function ( $xpath )
            {
                return array( $xpath );
            },
            $xpathAssertions
        );
    }

    /**
     * @param string $xpath
     * @param \DOMDocument $dom
     *
     * @depends testVisitRestFieldDefinition
     * @dataProvider provideXpathAssertions
     */
    public function testGeneratedXml( $xpath, \DOMDocument $dom )
    {
        $this->assertXPath( $dom, $xpath );
    }

    /**
     * Get the Content visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestFieldDefinition
     */
    protected function getContentVisitor()
    {
        return new ValueObjectVisitor\RestFieldDefinition(
            new Common\UrlHandler\eZPublish(),
            $this->fieldTypeSerializerMock
        );
    }
}
