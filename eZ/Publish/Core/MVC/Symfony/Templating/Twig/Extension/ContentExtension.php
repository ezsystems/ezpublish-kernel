<?php
/**
 * File containing the ContentExtension class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;
use Twig_Environment;
use Twig_Function_Method;
use Twig_Filter_Method;
use Twig_Template;
use InvalidArgumentException;
use LogicException;

/**
 * Twig content extension for eZ Publish specific usage.
 * Exposes helpers to play with public API objects.
 */
class ContentExtension extends Twig_Extension
{
    /**
     * Array of Twig template resources for ez_render_field
     * Either the path to each template and its priority in a hash or its
     * \Twig_Template (compiled) counterpart
     *
     * @var array|\Twig_Template[]
     */
    protected $renderFieldRessources;

    /**
     * Array of Twig template resources for ez_render_fielddefinition_settings
     * Either the path to each template and its priority in a hash or its
     * \Twig_Template (compiled) counterpart
     *
     * @var array|\Twig_Template[]
     */
    protected $renderFieldDefinitionSettingsResources;

    /**
     * A \Twig_Template instance used to render template blocks.
     *
     * @var \Twig_Template
     */
    protected $template;

    /**
     * The Twig environment
     *
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * Template blocks
     *
     * @var array
     */
    protected $blocks;

    /**
     * Converter used to transform XmlText content in HTML5
     *
     * @var \eZ\Publish\Core\FieldType\XmlText\Converter\Html5
     */
    protected $xmlTextConverter;

    /**
     * Hash of field type identifiers (i.e. "ezstring"), indexed by field definition identifier
     *
     * @var array
     */
    protected $fieldTypeIdentifiers = array();

    /**
     * @var \eZ\Publish\SPI\Variation\VariationHandler
     */
    protected $imageVariationService;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    public function __construct( ContainerInterface $container, ConfigResolverInterface $resolver )
    {
        $comp = function ( $a, $b )
        {
            return $b['priority'] - $a['priority'];
        };
        $this->renderFieldRessources = $resolver->getParameter( 'field_templates' );
        $this->renderFieldDefinitionSettingsResources = $resolver->getParameter(
            'fielddefinition_settings_templates'
        );
        usort( $this->renderFieldRessources, $comp );
        usort( $this->renderFieldDefinitionSettingsResources, $comp );

        $this->blocks = array();
        $this->container = $container;
        $this->configResolver = $resolver;
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
            'ez_render_field' => new Twig_Function_Method(
                $this,
                'renderField',
                array( 'is_safe' => array( 'html' ) )
            ),
            'ez_render_fielddefinition_settings' => new Twig_Function_Method(
                $this,
                'renderFieldDefinitionSettings',
                array( 'is_safe' => array( 'html' ) )
            ),
            'ez_image_alias' => new Twig_Function_Method( $this, 'getImageVariation' )
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
            'xmltext_to_html5' => new Twig_Filter_Method( $this, 'xmltextToHtml5' ),
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
     * Generates the array of parameter to pass to the field template.
     *
     * @param \eZ\Publish\Core\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field the Field to display
     * @param array $params An array of parameters to pass to the field view
     *
     * @return array
     */
    protected function getRenderFieldBlockParameters(
        Content $content, Field $field, array $params = array()
    )
    {
        // Merging passed parameters to default ones
        $params += array(
            'parameters' => array(), // parameters dedicated to template processing
            'attr' => array() // attributes to add on the enclosing HTML tags
        );

        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->container->get( 'ezpublish.api.repository' );
        /** @var $parameterProviderRegistry \eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistryInterface */
        $parameterProviderRegistry = $this->container->get( 'ezpublish.fieldType.parameterProviderRegistry' );

        $versionInfo = $content->getVersionInfo();
        $contentInfo = $versionInfo->getContentInfo();
        $contentType = $repository->getContentTypeService()->loadContentType( $contentInfo->contentTypeId );
        $fieldDefinition = $contentType->getFieldDefinition( $field->fieldDefIdentifier );
        // Adding Field, FieldSettings and ContentInfo objects to
        // parameters to be passed to the template
        $params += array(
            'field' => $field,
            'contentInfo' => $contentInfo,
            'versionInfo' => $versionInfo,
            'fieldSettings' => $fieldDefinition->getFieldSettings()
        );

        // Adding field type specific parameters if any.
        if ( $parameterProviderRegistry->hasParameterProvider( $fieldDefinition->fieldTypeIdentifier ) )
        {
            $params['parameters'] += $parameterProviderRegistry
                ->getParameterProvider( $fieldDefinition->fieldTypeIdentifier )
                ->getViewParameters();
        }

        // make sure we can easily add class="<fieldtypeidentifier>-field" to the
        // generated HTML
        if ( isset( $params['attr']['class'] ) )
        {
            $params['attr']['class'] .= ' ' . $this->getFieldTypeIdentifier( $content, $field ) . '-field';
        }
        else
        {
            $params['attr']['class'] = $this->getFieldTypeIdentifier( $content, $field ) . '-field';
        }
        return $params;
    }

    /**
     * Renders the HTML for the settings for the given field definition
     * $definition
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $definition
     * @return string
     */
    public function renderFieldDefinitionSettings( FieldDefinition $definition )
    {
        if ( !$this->template instanceof Twig_Template )
        {
            $tpl = reset( $this->renderFieldDefinitionSettingsResources );
            $this->template = $this->environment->loadTemplate( $tpl['template'] );
        }
        $parameters = array(
            'fielddefinition' => $definition,
            'settings' => $definition->getFieldSettings(),
        );

        return $this->template->renderBlock(
            $this->getRenderFieldDefinitionSettingsBlockName( $definition ),
            $parameters,
            $this->getBlockByFieldDefinition( $definition )
        );
    }

    /**
     * Renders the HTML for a given field.
     *
     * @param \eZ\Publish\Core\Repository\Values\Content\Content $content
     * @param string $fieldIdentifier Identifier for the field we want to render
     * @param array $params An array of parameters to pass to the field view
     * @throws \InvalidArgumentException If $fieldIdentifier is invalid in $content
     * @return string The HTML markup
     */
    public function renderField( Content $content, $fieldIdentifier, array $params = array() )
    {
        if ( !isset( $params['lang'] ) )
        {
            $languages = $this->configResolver->getParameter( 'languages' );
        }
        else
        {
            $languages = array( $params['lang'] );
            unset( $params['lang'] );
        }
        // Always add null as last entry so that we can use it pass it as languageCode $content->getField(),
        // forcing to use the main language if all others fail.
        $languages[] = null;

        // Loop over prioritized languages to get the appropriate translated field.
        foreach ( $languages as $lang )
        {
            $field = $content->getField( $fieldIdentifier, $lang );
            if ( $field instanceof Field )
                break;
        }

        if ( !$field instanceof Field )
        {
            throw new InvalidArgumentException(
                "Invalid field identifier '$fieldIdentifier' for content #{$content->contentInfo->id}"
            );
        }

        $localTemplate = null;
        if ( isset( $params['template'] ) )
        {
            // local override of the template
            // this template is put on the top the templates stack
            $localTemplate = $params['template'];
            unset( $params['template'] );
        }

        $params = $this->getRenderFieldBlockParameters( $content, $field, $params );

        // Getting instance of Twig_Template that will be used to render blocks
        if ( !$this->template instanceof Twig_Template )
        {
            $tpl = reset( $this->renderFieldRessources );
            $this->template = $this->environment->loadTemplate( $tpl['template'] );
        }

        return $this->template->renderBlock(
            $this->getRenderFieldBlockName( $content, $field ),
            $params,
            $this->getBlocksByField( $content, $field, $localTemplate )
        );
    }

    /**
     * @return \eZ\Publish\Core\FieldType\XmlText\Converter\Html5
     */
    protected function getXmlTextConverter()
    {
        if ( !isset( $this->xmlTextConverter ) )
            $this->xmlTextConverter = $this->container->get( "ezpublish.fieldType.ezxmltext.converter.html5" );

        return $this->xmlTextConverter;
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
        return $this->getXmlTextConverter()->convert( $xmlData );
    }

    /**
     * Returns the image variant object for $field/$versionInfo
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param string $variationName
     *
     * @return \eZ\Publish\API\Repository\Values\File\ImageVariant
     */
    public function getImageVariation( Field $field, VersionInfo $versionInfo, $variationName )
    {
        if ( !isset( $this->imageVariationService ) )
            $this->imageVariationService = $this->container->get( 'ezpublish.fieldType.ezimage.variation_service' );

        return $this->imageVariationService->getVariation( $field, $versionInfo, $variationName );
    }

    /**
     * Returns the block named $blockName in the given template. If it's not
     * found, returns null.
     *
     * @param string $blockName
     * @param \Twig_Template $tpl
     *
     * @return array|null
     */
    protected function searchBlock( $blockName, Twig_Template $tpl )
    {
        // Current template might have parents, so we need to loop against
        // them to find a matching block
        do
        {
            foreach ( $tpl->getBlocks() as $name => $block )
            {
                if ( $name === $blockName )
                {
                    return $block;
                }
            }
        }
        while ( ( $tpl = $tpl->getParent( array() ) ) instanceof Twig_Template );

        return null;
    }

    /**
     * Returns template blocks for $field. First check in the $localTemplate if
     * it's provided.
     * Template block convention name is <fieldTypeIdentifier>_field
     * Example: 'ezstring_field' will be relevant for a full view of ezstring field type
     *
     * @param Content $content
     * @param Field $field
     * @param null|string $localTemplate a file where to look for the block first
     *
     * @throws \LogicException If no template block can be found for $field
     *
     * @return array
     */
    protected function getBlocksByField( Content $content, Field $field, $localTemplate = null )
    {
        $fieldBlockName = $this->getRenderFieldBlockName( $content, $field );
        if ( $localTemplate !== null )
        {
            $tpl = $this->environment->loadTemplate( $localTemplate );
            $block = $this->searchBlock( $fieldBlockName, $tpl );
            if ( $block !== null )
            {
                return array( $fieldBlockName => $block );
            }
        }
        return $this->getBlockByName( $fieldBlockName, 'renderFieldRessources' );
    }

    /**
     * Returns the template block for the settings of the field definition $definition.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $definition
     * @return array
     */
    protected function getBlockByFieldDefinition( FieldDefinition $definition )
    {
        return $this->getBlockByName(
            $this->getRenderFieldDefinitionSettingsBlockName( $definition ),
            'renderFieldDefinitionSettingsResources'
        );
    }

    /**
     * Returns the template block of the given $name available in the resources
     * which name is $resourcesName
     *
     * @param string $name
     * @param string $resourcesName
     *
     * @throws \LogicException If no template block can be found for $field
     *
     * @return array
     */
    protected function getBlockByName( $name, $resourcesName )
    {
        if ( isset( $this->blocks[$name] ) )
        {
            return array( $name => $this->blocks[$name] );
        }

        $blocks = array();
        foreach ( $this->{$resourcesName} as &$template )
        {
            if ( !$template instanceof Twig_Template )
                $template = $this->environment->loadTemplate( $template['template'] );

            $tpl = $template;

            $block = $this->searchBlock( $name, $tpl );
            if ( $block !== null )
            {
                $this->blocks[$name] = $block;
                return array( $name => $block );
            }
        }
        throw new LogicException( "Cannot find '$name' template block." );
    }

    /**
     * Returns expected block name for $field, attached in $content.
     *
     * @param \eZ\Publish\Core\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     *
     * @return string
     */
    protected function getRenderFieldBlockName( Content $content, Field $field )
    {
        return $this->getFieldTypeIdentifier( $content, $field ) . '_field';
    }

    /**
     * Returns the name of the block to render the settings of the field
     * definition $definition
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $definition
     * @return string
     */
    protected function getRenderFieldDefinitionSettingsBlockName( FieldDefinition $definition )
    {
        return $definition->fieldTypeIdentifier . '_settings';
    }

    /**
     * Returns the field type identifier for $field
     *
     * @param \eZ\Publish\Core\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     *
     * @return string
     */
    protected function getFieldTypeIdentifier( Content $content, Field $field )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->container->get( 'ezpublish.api.repository' );
        $contentType = $repository->getContentTypeService()->loadContentType(
            $content->getVersionInfo()->getContentInfo()->contentTypeId
        );
        $key = $contentType->identifier . '  ' . $field->fieldDefIdentifier;

        if ( !isset( $this->fieldTypeIdentifiers[$key] ) )
        {
            $this->fieldTypeIdentifiers[$key] = $contentType
                ->getFieldDefinition( $field->fieldDefIdentifier )
                ->fieldTypeIdentifier;
        }

        return $this->fieldTypeIdentifiers[$key];
    }
}
