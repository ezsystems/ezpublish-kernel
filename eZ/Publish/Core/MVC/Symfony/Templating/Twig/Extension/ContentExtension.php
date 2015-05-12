<?php
/**
 * File containing the ContentExtension class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\Core\MVC\Exception\SourceImageNotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Helper\FieldHelper;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\FieldType\XmlText\Converter\Html5 as Html5Converter;
use eZ\Publish\Core\FieldType\RichText\Converter as RichTextConverterInterface;
use eZ\Publish\SPI\Variation\VariationHandler;
use eZ\Publish\API\Repository\Exceptions\InvalidVariationException;
use Psr\Log\LoggerInterface;
use Twig_Extension;
use Twig_Environment;
use Twig_SimpleFunction;
use Twig_SimpleFilter;

/**
 * Twig content extension for eZ Publish specific usage.
 * Exposes helpers to play with public API objects.
 */
class ContentExtension extends Twig_Extension
{
    /**
     * The Twig environment
     *
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * Converter used to transform XmlText content in HTML5
     *
     * @var \eZ\Publish\Core\FieldType\XmlText\Converter\Html5
     */
    protected $xmlTextConverter;

    /**
     * Converter used to transform RichText content to HTML5 for rendering purposes
     *
     * @var \eZ\Publish\Core\FieldType\RichText\Converter
     */
    protected $richTextConverter;

    /**
     * Converter used to transform RichText content to HTML5 for editing purposes
     *
     * @var \eZ\Publish\Core\FieldType\RichText\Converter
     */
    protected $richTextEditConverter;

    /**
     * @var \eZ\Publish\SPI\Variation\VariationHandler
     */
    protected $imageVariationService;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * @var \eZ\Publish\Core\Helper\FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Repository $repository,
        Html5Converter $xmlTextConverter,
        RichTextConverterInterface $richTextConverter,
        RichTextConverterInterface $richTextEditConverter,
        VariationHandler $imageVariationService,
        TranslationHelper $translationHelper,
        FieldHelper $fieldHelper,
        LoggerInterface $logger = null
    )
    {
        $this->repository = $repository;
        $this->xmlTextConverter = $xmlTextConverter;
        $this->richTextConverter = $richTextConverter;
        $this->richTextEditConverter = $richTextEditConverter;
        $this->imageVariationService = $imageVariationService;
        $this->translationHelper = $translationHelper;
        $this->fieldHelper = $fieldHelper;
        $this->logger = $logger;
    }

    /**
     * Initializes the template runtime (aka Twig environment).
     *
     * @param \Twig_Environment $environment
     */
    public function initRuntime( Twig_Environment $environment )
    {
        $this->environment = $environment;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction(
                'ez_image_alias',
                array( $this, 'getImageVariation' ),
                array( 'is_safe' => array( 'html' ) )
            ),
            new Twig_SimpleFunction(
                'ez_content_name',
                array( $this, 'getTranslatedContentName' )
            ),
            new Twig_SimpleFunction(
                'ez_field_value',
                array( $this, 'getTranslatedFieldValue' )
            ),
            new Twig_SimpleFunction(
                'ez_is_field_empty',
                array( $this, 'isFieldEmpty' )
            ),
            new Twig_SimpleFunction(
                'ez_field_name',
                array( $this, 'getTranslatedFieldDefinitionName' )
            ),
            new Twig_SimpleFunction(
                'ez_field_description',
                array( $this, 'getTranslatedFieldDefinitionDescription' )
            ),
            new Twig_SimpleFunction(
                'ez_trans_prop',
                array( $this, 'getTranslatedProperty' )
            ),
        );
    }

    /**
     * Returns a list of filters to add to the existing list
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter(
                'xmltext_to_html5',
                array( $this, 'xmlTextToHtml5' ),
                array( 'is_safe' => array( 'html' ) )
            ),
            new Twig_SimpleFilter(
                'richtext_to_html5',
                array( $this, 'richTextToHtml5' ),
                array( 'is_safe' => array( 'html' ) )
            ),
            new Twig_SimpleFilter(
                'richtext_to_html5_edit',
                array( $this, 'richTextToHtml5Edit' ),
                array( 'is_safe' => array( 'html' ) )
            )
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'ezpublish.content';
    }

    /**
     * Implements the "xmltext_to_html5" filter
     *
     * @param string $xmlData
     *
     * @return string
     */
    public function xmltextToHtml5( $xmlData )
    {
        return $this->xmlTextConverter->convert( $xmlData );
    }

    /**
     * Implements the "richtext_to_html5" filter
     *
     * @param \DOMDocument $xmlData
     *
     * @return string
     */
    public function richTextToHtml5( $xmlData )
    {
        return $this->richTextConverter->convert( $xmlData )->saveHTML();
    }

    /**
     * Implements the "richtext_to_html5_edit" filter
     *
     * @param \DOMDocument $xmlData
     *
     * @return string
     */
    public function richTextToHtml5Edit( $xmlData )
    {
        return $this->richTextEditConverter->convert( $xmlData )->saveHTML();
    }

    /**
     * Returns the image variation object for $field/$versionInfo
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param string $variationName
     *
     * @return \eZ\Publish\SPI\Variation\Values\Variation
     */
    public function getImageVariation( Field $field, VersionInfo $versionInfo, $variationName )
    {
        try
        {
            return $this->imageVariationService->getVariation( $field, $versionInfo, $variationName );
        }
        catch ( InvalidVariationException $e )
        {
            if ( isset( $this->logger ) )
            {
                $this->logger->error( "Couldn't get variation '{$variationName}' for image with id {$field->value->id}" );
            }
        }
        catch ( SourceImageNotFoundException $e )
        {
            if ( isset( $this->logger ) )
            {
                $this->logger->error(
                    "Couldn't create variation '{$variationName}' for image with id {$field->value->id} because source image can't be found"
                );
            }
        }
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ValueObject $content Must be a valid Content or ContentInfo object.
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType When $content is not a valid Content or ContentInfo object.
     *
     * @return string
     */
    public function getTranslatedContentName( ValueObject $content, $forcedLanguage = null )
    {
        if ( $content instanceof Content )
        {
            return $this->translationHelper->getTranslatedContentName( $content, $forcedLanguage );
        }
        else if ( $content instanceof ContentInfo )
        {
            return $this->translationHelper->getTranslatedContentNameByContentInfo( $content, $forcedLanguage );
        }

        throw new InvalidArgumentType( '$content', 'eZ\Publish\API\Repository\Values\Content\Content or eZ\Publish\API\Repository\Values\Content\ContentInfo', $content );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldDefIdentifier Identifier for the field we want to get the value from.
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale).
     *
     * @return mixed A primitive type or a field type Value object depending on the field type.
     */
    public function getTranslatedFieldValue( Content $content, $fieldDefIdentifier, $forcedLanguage = null )
    {
        return $this->translationHelper->getTranslatedField( $content, $fieldDefIdentifier, $forcedLanguage )->value;
    }

    /**
     * Gets name of a FieldDefinition name by loading ContentType based on Content/ContentInfo object
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $content Must be Content or ContentInfo object
     * @param string $fieldDefIdentifier Identifier for the field we want to get the name from
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType When $content is not a valid Content object.
     *
     * @return string|null
     */
    public function getTranslatedFieldDefinitionName( ValueObject $content, $fieldDefIdentifier, $forcedLanguage = null )
    {
        if ( $contentType = $this->getContentType( $content ) )
        {
            return $this->translationHelper->getTranslatedFieldDefinitionProperty(
                $contentType,
                $fieldDefIdentifier,
                'name',
                $forcedLanguage
            );
        }

        throw new InvalidArgumentType( '$content', 'Content|ContentInfo', $content );
    }

    /**
     * Gets name of a FieldDefinition description by loading ContentType based on Content/ContentInfo object
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $content Must be Content or ContentInfo object
     * @param string $fieldDefIdentifier Identifier for the field we want to get the name from
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType When $content is not a valid Content object.
     *
     * @return string|null
     */
    public function getTranslatedFieldDefinitionDescription( ValueObject $content, $fieldDefIdentifier, $forcedLanguage = null )
    {
        if ( $contentType = $this->getContentType( $content ) )
        {
            return $this->translationHelper->getTranslatedFieldDefinitionProperty(
                $contentType,
                $fieldDefIdentifier,
                'description',
                $forcedLanguage
            );
        }

        throw new InvalidArgumentType( '$content', 'Content|ContentInfo', $content );
    }

    /**
     * Gets translated property generic helper
     *
     * For generic use, expects property in singular form. For instance if 'name' is provided it will first look for
     * getName( $lang ) method, then property called ->names[$lang], in either case look for correct translation.
     *
     * Languages will consist of either forced language or current SiteAccess languages list, in addition for property
     * lookup helper will look for mainLanguage property and use it if either alwaysAvailable property is true or non-
     * existing.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object Can be any kid of Value object which directly holds the translated data
     * @param string $property Property name, example 'name', 'description'
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue If $property does not exists as plural or as method
     *
     * @return string|null
     */
    public function getTranslatedProperty( ValueObject $object, $property, $forcedLanguage = null )
    {
        $pluralProperty = $property . 's';
        if ( method_exists( $object, 'get' . $property ) )
        {
            return $this->translationHelper->getTranslatedByMethod(
                $object,
                'get' . $property,
                $forcedLanguage
            );
        }
        else if ( property_exists( $object, $pluralProperty ) && is_array( $object->$pluralProperty ) )
        {
            return $this->translationHelper->getTranslatedByProperty(
                $object,
                $pluralProperty,
                $forcedLanguage
            );
        }

        throw new InvalidArgumentValue( '$property', $property, get_class( $object ) );
    }

    /**
     * Checks if a given field is considered empty.
     * This method accepts field as Objects or by identifiers.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Field|string $fieldDefIdentifier Field or Field Identifier to
     *                                                                                   get the value from.
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR").
     *                               Null by default (takes current locale).
     *
     * @return bool
     */
    public function isFieldEmpty( Content $content, $fieldDefIdentifier, $forcedLanguage = null )
    {
        if ( $fieldDefIdentifier instanceof Field )
        {
            $fieldDefIdentifier = $fieldDefIdentifier->fieldDefIdentifier;
        }

        return $this->fieldHelper->isFieldEmpty( $content, $fieldDefIdentifier, $forcedLanguage );
    }

    /**
     * Get ContentType by Content/ContentInfo
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content|\eZ\Publish\API\Repository\Values\Content\ContentInfo $content
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType|null
     */
    private function getContentType( ValueObject $content )
    {
        if ( $content instanceof Content )
        {
            return $this->repository->getContentTypeService()->loadContentType(
                $content->getVersionInfo()->getContentInfo()->contentTypeId
            );
        }
        else if ( $content instanceof ContentInfo )
        {
            return $this->repository->getContentTypeService()->loadContentType( $content->contentTypeId );
        }
    }
}
