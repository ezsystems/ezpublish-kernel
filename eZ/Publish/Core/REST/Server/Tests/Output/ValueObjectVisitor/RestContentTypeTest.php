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

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\Core\REST\Server\Values\RestContentType;

class RestContentTypeTest extends ValueObjectVisitorBaseTest
{
    /**
     * @return \DOMDocument
     */
    public function testVisitDefinedType()
    {
        $visitor   = $this->getRestContentTypeVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $restContentType = $this->getBasicContentType();

        $this->getVisitorMock()->expects( $this->once() )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\Core\\REST\\Server\\Values\\FieldDefinitionList' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $restContentType
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        $dom = new \DOMDocument();
        $dom->loadXml( $result );

        return $dom;
    }

    protected function getBasicContentType()
    {
        return new RestContentType(
            new Values\ContentType\ContentType(
                array(
                    'id' => 'contentTypeId',
                    'status' => Values\ContentType\ContentType::STATUS_DEFINED,
                    'identifier' => 'contentTypeIdentifier',
                    'creationDate' => new \DateTime( '2012-09-06 19:30 Europe/Berlin' ),
                    'modificationDate' => new \DateTime( '2012-09-06 19:32 Europe/Berlin' ),
                    'creatorId' => 'creatorId',
                    'modifierId' => 'modifierId',
                    'remoteId' => 'remoteId',
                    'urlAliasSchema' => 'urlAliasSchema',
                    'nameSchema' => 'nameSchema',
                    'isContainer' => true,
                    'mainLanguageCode' => 'eng-US',
                    'defaultAlwaysAvailable' => false,
                    'defaultSortField' => Values\Content\Location::SORT_FIELD_SECTION,
                    'defaultSortOrder' => Values\Content\Location::SORT_ORDER_DESC,

                    'names' => array( 'eng-US' => 'Sindelfingen', 'eng-GB' => 'Bielefeld' ),
                    'descriptions' => array( 'eng-GB' => 'Sindelfingen', 'eng-US' => 'Bielefeld' ),

                    // "Mock"
                    'fieldDefinitions' => array(),
                )
            ),
            array()
        );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testContentTypeHref( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType[@href="/content/types/contentTypeId"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testContentTypeMediaTyp( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType[@media-type="application/vnd.ez.api.ContentType+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testId( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/id[text()="contentTypeId"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testStatus( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/status[text()="DEFINED"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testIdentifier( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/identifier[text()="contentTypeIdentifier"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testFirstName( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/names/value[@languageCode="eng-US" and text()="Sindelfingen"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testSecondName( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/names/value[@languageCode="eng-GB" and text()="Bielefeld"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testFirstDescription( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/descriptions/value[@languageCode="eng-GB" and text()="Sindelfingen"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testSecondDescription( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/descriptions/value[@languageCode="eng-US" and text()="Bielefeld"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testCreationDate( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/creationDate[text()="2012-09-06T19:30:00+02:00"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testModificationDate( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/modificationDate[text()="2012-09-06T19:32:00+02:00"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testCreatorHref( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/Creator[@href="/user/users/creatorId"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testCreatorMediaType( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/Creator[@media-type="application/vnd.ez.api.User+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testModifierHref( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/Modifier[@href="/user/users/modifierId"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testModifierMediaType( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/Modifier[@media-type="application/vnd.ez.api.User+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testRemoteId( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/remoteId[text()="remoteId"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testUrlAliasSchema( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/urlAliasSchema[text()="urlAliasSchema"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testNameSchema( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/nameSchema[text()="nameSchema"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testIsContainer( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/isContainer[text()="true"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testMainLanguageCode( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/mainLanguageCode[text()="eng-US"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testDefaultAlwaysAvailable( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/defaultAlwaysAvailable[text()="false"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testDefaultSortField( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/defaultSortField[text()="SECTION"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitDefinedType
     */
    public function testDefaultSortOrder( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentType/defaultSortOrder[text()="DESC"]'  );
    }

    /**
     * Get the RestContentType visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestContentType
     */
    protected function getRestContentTypeVisitor()
    {
        return new ValueObjectVisitor\RestContentType(
            new Common\UrlHandler\eZPublish()
        );
    }
}
