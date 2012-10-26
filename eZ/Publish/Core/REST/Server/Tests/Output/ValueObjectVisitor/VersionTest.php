<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values;
use eZ\Publish\Core\REST\Server\Values\Version;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\API\Repository\Values\Content\Field;

class VersionTest extends ValueObjectVisitorBaseTest
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
     * Test the Version visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getVersionVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $version = new Version(
            new Values\Content\Content(
                array(
                    'versionInfo' => new Values\Content\VersionInfo(
                        array(
                            'versionNo' => 5,
                            'contentInfo' => new Values\Content\ContentInfo(
                                array(
                                    'id' => 23,
                                    'contentType' => new Values\ContentType\ContentType(
                                        array(
                                            'id' => 42,
                                            'fieldDefinitions' => array(),
                                        )
                                    ),
                                )
                            ),
                        )
                    ),
                    'internalFields' => array(
                        new Field(
                            array(
                                'id' => 1,
                                'languageCode' => 'eng-US',
                                'fieldDefIdentifier' => 'ezauthor',
                            )
                        ),
                        new Field(
                            array(
                                'id' => 2,
                                'languageCode' => 'eng-US',
                                'fieldDefIdentifier' => 'ezimage',
                            )
                        ),
                    ),
                    'relations' => array(),
                )
            )
        );

        $this->fieldTypeSerializerMock->expects( $this->exactly( 2 ) )
            ->method( 'serializeFieldValue' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\Core\\REST\\Common\\Output\\Generator' ),
                $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType' ),
                $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Field' )
            );

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $version
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsVersionChildren( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Version',
                'children' => array(
                    'less_than'    => 2,
                    'greater_than' => 0,
                )
            ),
            $result,
            'Invalid <Version> element.',
            false
        );
    }

    /**
     * @param string $result
     * @depends testVisit
     */
    public function testResultVersionAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Version',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Version+xml',
                    'href' => '/content/objects/23/versions/5'
                )
            ),
            $result,
            'Invalid <Version> attributes.',
            false
        );
    }

    /**
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsFieldsChildren( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Fields',
                'children' => array(
                    'less_than'    => 3,
                    'greater_than' => 1,
                )
            ),
            $result,
            'Invalid <Fields> element.',
            false
        );
    }

    /**
     * Get the Version visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Version
     */
    protected function getVersionVisitor()
    {
        return new ValueObjectVisitor\Version(
            new Common\UrlHandler\eZPublish(),
            $this->fieldTypeSerializerMock
        );
    }
}
