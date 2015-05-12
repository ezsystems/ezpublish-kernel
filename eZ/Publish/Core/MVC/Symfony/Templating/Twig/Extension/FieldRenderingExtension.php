<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistryInterface;
use LogicException;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;
use Twig_Template;

class FieldRenderingExtension extends Twig_Extension
{
    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var ParameterProviderRegistryInterface
     */
    private $parameterProviderRegistry;

    /**
     * Array of Twig template resources for ez_render_field
     * Either the path to each template and its priority in a hash or its
     * \Twig_Template (compiled) counterpart
     *
     * @var \Twig_Template[]
     */
    private $renderFieldResources;

    /**
     * Array of Twig template resources for ez_render_fielddefinition_settings
     * Either the path to each template and its priority in a hash or its
     * \Twig_Template (compiled) counterpart
     *
     * @var \Twig_Template[]
     */
    private $renderFieldDefinitionSettingsResources;

    /**
     * A \Twig_Template instance used to render template blocks.
     *
     * @var \Twig_Template
     */
    private $template;

    /**
     * The Twig environment
     *
     * @var \Twig_Environment
     */
    private $environment;

    /**
     * Template blocks
     *
     * @var array
     */
    private $blocks = array();

    /**
     * Hash of field type identifiers (i.e. "ezstring"), indexed by field definition identifier
     *
     * @var array
     */
    private $fieldTypeIdentifiers = array();

    /**
     * @var TranslationHelper
     */
    private $translationHelper;

    public function __construct(
        ConfigResolverInterface $configResolver,
        ContentTypeService $contentTypeService,
        ParameterProviderRegistryInterface $parameterProviderRegistry,
        TranslationHelper $translationHelper
    )
    {
        $this->configResolver = $configResolver;
        $this->contentTypeService = $contentTypeService;
        $this->parameterProviderRegistry = $parameterProviderRegistry;
        $this->translationHelper = $translationHelper;

        $comp = function ( $a, $b )
        {
            return $b['priority'] - $a['priority'];
        };
        $this->renderFieldResources = $configResolver->getParameter( 'field_templates' );
        $this->renderFieldDefinitionSettingsResources = $configResolver->getParameter( 'fielddefinition_settings_templates' );
        usort( $this->renderFieldResources, $comp );
        usort( $this->renderFieldDefinitionSettingsResources, $comp );
    }

    public function getName()
    {
        return 'ezpublish.field_rendering';
    }

    public function initRuntime( Twig_Environment $environment )
    {
        $this->environment = $environment;
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction(
                'ez_render_field',
                array( $this, 'renderField' ),
                array( 'is_safe' => array( 'html' ) )
            ),
            new Twig_SimpleFunction(
                'ez_render_fielddefinition_settings',
                array( $this, 'renderFieldDefinitionSettings' ),
                array( 'is_safe' => array( 'html' ) )
            ),
        );
    }

    /**
     * Generates the array of parameter to pass to the field template.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field the Field to display
     * @param array $params An array of parameters to pass to the field view
     *
     * @return array
     */
    protected function getRenderFieldBlockParameters( Content $content, Field $field, array $params = array() )
    {
        // Merging passed parameters to default ones
        $params += array(
            'parameters' => array(), // parameters dedicated to template processing
            'attr' => array() // attributes to add on the enclosing HTML tags
        );

        $versionInfo = $content->getVersionInfo();
        $contentInfo = $versionInfo->getContentInfo();
        $contentType = $this->contentTypeService->loadContentType( $contentInfo->contentTypeId );
        $fieldDefinition = $contentType->getFieldDefinition( $field->fieldDefIdentifier );
        // Adding Field, FieldSettings and ContentInfo objects to
        // parameters to be passed to the template
        $params += array(
            'field' => $field,
            'content' => $content,
            'contentInfo' => $contentInfo,
            'versionInfo' => $versionInfo,
            'fieldSettings' => $fieldDefinition->getFieldSettings()
        );

        // Adding field type specific parameters if any.
        if ( $this->parameterProviderRegistry->hasParameterProvider( $fieldDefinition->fieldTypeIdentifier ) )
        {
            $params['parameters'] += $this->parameterProviderRegistry
                ->getParameterProvider( $fieldDefinition->fieldTypeIdentifier )
                ->getViewParameters( $field );
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
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldIdentifier Identifier for the field we want to render
     * @param array $params An array of parameters to pass to the field view
     *
     * @throws InvalidArgumentException
     * @return string The HTML markup
     */
    public function renderField( Content $content, $fieldIdentifier, array $params = array() )
    {
        $field = $this->translationHelper->getTranslatedField( $content, $fieldIdentifier, isset( $params['lang'] ) ? $params['lang'] : null );

        if ( !$field instanceof Field )
        {
            throw new InvalidArgumentException(
                '$fieldIdentifier',
                "'{$fieldIdentifier}' field not present on content #{$content->contentInfo->id} '{$content->contentInfo->name}'"
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
            $tpl = reset( $this->renderFieldResources );
            $this->template = $this->environment->loadTemplate( $tpl['template'] );
        }

        return $this->template->renderBlock(
            $this->getRenderFieldBlockName( $content, $field ),
            $params,
            $this->getBlocksByField( $content, $field, $localTemplate )
        );
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
     * @param null|string|\Twig_Template $localTemplate a file where to look for the block first
     *
     * @throws LogicException If no template block can be found for $field
     *
     * @return array
     */
    protected function getBlocksByField( Content $content, Field $field, $localTemplate = null )
    {
        $fieldBlockName = $this->getRenderFieldBlockName( $content, $field );
        if ( $localTemplate !== null )
        {
            // $localTemplate might be a Twig_Template instance already (e.g. using _self Twig keyword)
            if ( !$localTemplate instanceof Twig_Template )
            {
                $localTemplate = $this->environment->loadTemplate( $localTemplate );
            }

            $block = $this->searchBlock( $fieldBlockName, $localTemplate );
            if ( $block !== null )
            {
                return array( $fieldBlockName => $block );
            }
        }
        return $this->getBlockByName( $fieldBlockName, 'renderFieldResources' );
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
     * @throws LogicException If no template block can be found for $field
     *
     * @return array
     */
    protected function getBlockByName( $name, $resourcesName )
    {
        if ( isset( $this->blocks[$name] ) )
        {
            return array( $name => $this->blocks[$name] );
        }

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
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
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
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     *
     * @return string
     */
    protected function getFieldTypeIdentifier( Content $content, Field $field )
    {
        $contentInfo = $content->getVersionInfo()->getContentInfo();
        $key = $contentInfo->contentTypeId . '  ' . $field->fieldDefIdentifier;

        if ( !isset( $this->fieldTypeIdentifiers[$key] ) )
        {
            $contentType = $this->contentTypeService->loadContentType( $contentInfo->contentTypeId );
            $this->fieldTypeIdentifiers[$key] = $contentType
                ->getFieldDefinition( $field->fieldDefIdentifier )
                ->fieldTypeIdentifier;
        }

        return $this->fieldTypeIdentifiers[$key];
    }
}
