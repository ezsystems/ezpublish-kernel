<?php
/**
 * File containing the ContentExtensionIntegrationTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension;

use eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\ContentExtension;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;

class ContentExtensionIntegrationTest extends FileSystemTwigIntegrationTestCase
{
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldHelperMock;

    public function getExtensions()
    {
        $configResolver = $this->getConfigResolverMock();
        $this->fieldHelperMock = $this->getMockBuilder( 'eZ\\Publish\\Core\\Helper\\FieldHelper' )
            ->disableOriginalConstructor()->getMock();

        return array(
            new ContentExtension(
                $this->getRepositoryMock(),
                $configResolver,
                $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\FieldType\\View\\ParameterProviderRegistryInterface' ),
                $this->getMockBuilder( 'eZ\Publish\Core\FieldType\XmlText\Converter\Html5' )->disableOriginalConstructor()->getMock(),
                $this->getMockBuilder( 'eZ\\Publish\\Core\\FieldType\\RichText\\Converter' )->disableOriginalConstructor()->getMock(),
                $this->getMockBuilder( 'eZ\\Publish\\Core\\FieldType\\RichText\\Converter' )->disableOriginalConstructor()->getMock(),
                $this->getMock( 'eZ\Publish\SPI\Variation\VariationHandler' ),
                new TranslationHelper(
                    $configResolver,
                    $this->getMock( 'eZ\\Publish\\API\\Repository\\ContentService' ),
                    array(),
                    $this->getMock( 'Psr\Log\LoggerInterface' )
                ),
                $this->fieldHelperMock
            )
        );
    }

    public function getFixturesDir()
    {
        return dirname( __FILE__ ) . '/_fixtures/content_functions/';
    }

    public function getFieldDefinition( $typeIdentifier, $id = null, $settings = array() )
    {
        return new FieldDefinition(
            array(
                'id' => $id,
                'fieldSettings' => $settings,
                'fieldTypeIdentifier' => $typeIdentifier
            )
        );
    }

    public $fieldDefinitions = array();

    protected function getContent( $contentTypeIdentifier, $fieldsInfo )
    {
        $fields = array();
        foreach ( $fieldsInfo as $type => $info )
        {
            $fields[] = new Field( $info );
            // Save field definitions in property for mocking purposes
            $this->fieldDefinitions[$contentTypeIdentifier][] = new FieldDefinition(
                array(
                    'identifier' => $info['fieldDefIdentifier'],
                    'id' => $info['id'],
                    'fieldTypeIdentifier' => $type,
                )
            );
        }
        $content = new Content(
            array(
                'internalFields' => $fields,
                'versionInfo' => new VersionInfo(
                    array(
                        'versionNo' => 64,
                        'contentInfo' => new ContentInfo(
                            array(
                                'id' => 42,
                                'mainLanguageCode' => 'fre-FR',
                                // Using as id as we don't really care to test the service here
                                'contentTypeId' => $contentTypeIdentifier
                            )
                        )
                    )
                )
            )
        );

        return $content;

    }

    protected function getField( $isEmpty )
    {
        $field = new Field( array( 'fieldDefIdentifier' => 'testfield', 'value' => null ) );

        $this->fieldHelperMock
            ->expects( $this->once() )
            ->method( 'isFieldEmpty' )
            ->will( $this->returnValue( $isEmpty ) );

        return $field;
    }

    private function getTemplatePath( $tpl )
    {
        return 'templates/' . $tpl;
    }

    private function getConfigResolverMock()
    {
        $mock = $this->getMock(
            'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface'
        );
        $mock->expects( $this->any() )
            ->method( 'getParameter' )
            ->will(
                $this->returnValueMap(
                    array(
                        array(
                            'field_templates',
                            null,
                            null,
                            array(
                                array(
                                    'template' => $this->getTemplatePath( 'fields_override1.html.twig' ),
                                    'priority' => 10
                                ),
                                array(
                                    'template' => $this->getTemplatePath( 'fields_default.html.twig' ),
                                    'priority' => 0
                                ),
                                array(
                                    'template' => $this->getTemplatePath( 'fields_override2.html.twig' ),
                                    'priority' => 20
                                ),
                            )
                        ),
                        array(
                            'fielddefinition_settings_templates',
                            null,
                            null,
                            array(
                                array(
                                    'template' => $this->getTemplatePath( 'settings_override1.html.twig' ),
                                    'priority' => 10
                                ),
                                array(
                                    'template' => $this->getTemplatePath( 'settings_default.html.twig' ),
                                    'priority' => 0
                                ),
                                array(
                                    'template' => $this->getTemplatePath( 'settings_override2.html.twig' ),
                                    'priority' => 20
                                ),
                            )
                        )
                    )
                )
            );
        return $mock;
    }

    private function getContainerMock()
    {
        $mock = $this->getMock(
            'Symfony\\Component\\DependencyInjection\\ContainerInterface'
        );

        $mock->expects( $this->any() )
            ->method( "get" )
            ->with(
                $this->logicalOr(
                    $this->equalTo( "ezpublish.api.repository" ),
                    $this->equalTo( "ezpublish.fieldType.ezxmltext.converter.html5" ),
                    $this->equalTo( "ezpublish.fieldType.ezimage.variation_service" ),
                    $this->equalTo( "ezpublish.fieldType.parameterProviderRegistry" )
                )
            )
            ->will(
                $this->returnCallback(
                    array( $this, "containerMockCallback" )
                )
            );

        return $mock;
    }

    /**
     * Callback multiplexer for Container::get().
     *
     * @param $id
     *
     * @return mixed
     */
    public function containerMockCallback( $id )
    {
        switch ( $id )
        {
            case "ezpublish.api.repository":
                return $this->getRepositoryMock();

            case "ezpublish.fieldType.parameterProviderRegistry":
                return $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\FieldType\\View\\ParameterProviderRegistryInterface' );

            case "ezpublish.fieldType.ezxmltext.converter.html5":
            case "ezpublish.fieldType.ezimage.variation_service":
        }

        return null;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock()
    {
        $mock = $this->getMock( "eZ\\Publish\\API\\Repository\\Repository" );

        $mock->expects( $this->any() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $this->getContentTypeServiceMock() ) );

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContentTypeServiceMock()
    {
        $mock = $this->getMock( "eZ\\Publish\\API\\Repository\\ContentTypeService" );

        $context = $this;
        $mock->expects( $this->any() )
            ->method( "loadContentType" )
            ->will(
                $this->returnCallback(
                    function ( $contentTypeId ) use ( $context )
                    {
                        return new ContentType(
                            array(
                                'identifier' => $contentTypeId,
                                'fieldDefinitions' => $context->fieldDefinitions[$contentTypeId]
                            )
                        );
                    }
                )
            );

        return $mock;
    }
}
