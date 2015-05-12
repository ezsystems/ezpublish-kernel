<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistryInterface;
use eZ\Publish\Core\MVC\Symfony\Templating\FieldBlockRendererInterface;
use LogicException;
use Twig_Environment;
use Twig_Template;

class FieldBlockRenderer implements FieldBlockRendererInterface
{
    const VIEW = 1;
    const EDIT = 2;

    const FIELD_VIEW_SUFFIX = '_field';
    const FIELD_EDIT_SUFFIX = '_field_edit';
    const FIELD_DEFINITION_VIEW_SUFFIX = '_settings';
    const FIELD_DEFINITION_EDIT_SUFFIX = '_field_definition_edit';

    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var ParameterProviderRegistryInterface
     */
    private $parameterProviderRegistry;

    /**
     * @var TranslationHelper
     */
    private $translationHelper;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * Array of Twig template resources for field view.
     * Either the path to each template and its priority in a hash or its
     * \Twig_Template (compiled) counterpart
     *
     * @var Twig_Template[]|array
     */
    private $fieldViewResources = [];

    /**
     * Array of Twig template resources for field edit.
     * Either the path to each template and its priority in a hash or its
     * \Twig_Template (compiled) counterpart
     *
     * @var Twig_Template[]|array
     */
    private $fieldEditResources = [];

    /**
     * Array of Twig template resources for field definition view.
     * Either the path to each template and its priority in a hash or its
     * \Twig_Template (compiled) counterpart
     *
     * @var Twig_Template[]|array
     */
    private $fieldDefinitionViewResources = [];

    /**
     * Array of Twig template resources for field definition edit.
     * Either the path to each template and its priority in a hash or its
     * \Twig_Template (compiled) counterpart
     *
     * @var Twig_Template[]|array
     */
    private $fieldDefinitionEditResources = [];

    /**
     * A \Twig_Template instance used to render template blocks.
     *
     * @var Twig_Template
     */
    private $template;

    /**
     * Template blocks
     *
     * @var array
     */
    private $blocks = [];

    /**
     * Hash of field type identifiers (i.e. "ezstring"), indexed by field definition identifier
     *
     * @var array
     */
    private $fieldTypeIdentifiers = [];

    public function __construct(
        ContentTypeService $contentTypeService,
        ParameterProviderRegistryInterface $parameterProviderRegistry,
        TranslationHelper $translationHelper
    )
    {
        $this->contentTypeService = $contentTypeService;
        $this->parameterProviderRegistry = $parameterProviderRegistry;
        $this->translationHelper = $translationHelper;
    }

    /**
     * @param Twig_Environment $twig
     */
    public function setTwig( Twig_Environment $twig )
    {
        $this->twig = $twig;
    }

    /**
     * @param array $fieldViewResources
     */
    public function setFieldViewResources( array $fieldViewResources = null )
    {
        $this->fieldViewResources = (array)$fieldViewResources;
        usort( $this->fieldViewResources, [$this, 'sortResourcesCallback'] );
    }

    /**
     * @param array $fieldEditResources
     */
    public function setFieldEditResources( array $fieldEditResources = null )
    {
        $this->fieldEditResources = (array)$fieldEditResources;
        usort( $this->fieldEditResources, [$this, 'sortResourcesCallback'] );
    }

    /**
     * @param array $fieldDefinitionViewResources
     */
    public function setFieldDefinitionViewResources( array $fieldDefinitionViewResources = null )
    {
        $this->fieldDefinitionViewResources = (array)$fieldDefinitionViewResources;
        usort( $this->fieldDefinitionViewResources, [$this, 'sortResourcesCallback'] );
    }

    /**
     * @param array $fieldDefinitionEditResources
     */
    public function setFieldDefinitionEditResources( array $fieldDefinitionEditResources = null )
    {
        $this->fieldDefinitionEditResources = (array)$fieldDefinitionEditResources;
        usort( $this->fieldDefinitionEditResources, [$this, 'sortResourcesCallback'] );
    }

    public function sortResourcesCallback( array $a, array $b )
    {
        return $b['priority'] - $a['priority'];
    }

    public function renderContentFieldView( Content $content, $fieldIdentifier, array $params = [] )
    {
        return $this->renderContentField( $content, $fieldIdentifier, $params, self::VIEW );
    }

    public function renderContentFieldEdit( Content $content, $fieldIdentifier, array $params = [] )
    {
        return $this->renderContentField( $content, $fieldIdentifier, $params, self::EDIT );
    }

    /**
     * @param Content $content
     * @param string $fieldIdentifier
     * @param array $params
     * @param int $type Either self::VIEW or self::EDIT
     *
     * @return string
     * @throws InvalidArgumentException
     */
    private function renderContentField( Content $content, $fieldIdentifier, array $params, $type )
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
            $tpl = reset( $this->fieldViewResources );
            $this->template = $this->twig->loadTemplate( $tpl['template'] );
        }

        return $this->template->renderBlock(
            $this->getRenderFieldBlockName( $content, $field, $type ),
            $params,
            $this->getBlocksByField( $content, $field, $type, $localTemplate )
        );
    }

    public function renderFieldDefinitionView( FieldDefinition $fieldDefinition, array $params = [] )
    {
        return $this->renderFieldDefinition( $fieldDefinition, $params, self::VIEW );
    }

    public function renderFieldDefinitionEdit( FieldDefinition $fieldDefinition, array $params = [] )
    {
        return $this->renderFieldDefinition( $fieldDefinition, $params, self::EDIT );
    }

    /**
     * @param FieldDefinition $fieldDefinition
     * @param array $params
     * @param int $type Either self::VIEW or self::EDIT
     *
     * @return string
     */
    private function renderFieldDefinition( FieldDefinition $fieldDefinition, array $params, $type )
    {
        if ( !$this->template instanceof Twig_Template )
        {
            $tpl = reset( $this->fieldDefinitionViewResources );
            $this->template = $this->twig->loadTemplate( $tpl['template'] );
        }

        $params += [
            'fielddefinition' => $fieldDefinition,
            'settings' => $fieldDefinition->getFieldSettings(),
        ];

        return $this->template->renderBlock(
            $this->getRenderFieldDefinitionBlockName( $fieldDefinition, $type ),
            $params,
            $this->getBlocksByFieldDefinition( $fieldDefinition, $type )
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
    private function getRenderFieldBlockParameters( Content $content, Field $field, array $params = [] )
    {
        // Merging passed parameters to default ones
        $params += [
            'parameters' => [], // parameters dedicated to template processing
            'attr' => [] // attributes to add on the enclosing HTML tags
        ];

        $versionInfo = $content->getVersionInfo();
        $contentInfo = $versionInfo->getContentInfo();
        $contentType = $this->contentTypeService->loadContentType( $contentInfo->contentTypeId );
        $fieldDefinition = $contentType->getFieldDefinition( $field->fieldDefIdentifier );
        // Adding Field, FieldSettings and ContentInfo objects to
        // parameters to be passed to the template
        $params += [
            'field' => $field,
            'content' => $content,
            'contentInfo' => $contentInfo,
            'versionInfo' => $versionInfo,
            'fieldSettings' => $fieldDefinition->getFieldSettings()
        ];

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
     * Returns the block named $blockName in the given template. If it's not
     * found, returns null.
     *
     * @param string $blockName
     * @param Twig_Template $tpl
     *
     * @return array|null
     */
    private function searchBlock( $blockName, Twig_Template $tpl )
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
        while ( ( $tpl = $tpl->getParent( [] ) ) instanceof Twig_Template );

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
     * @param int $type Either self::VIEW or self::EDIT
     * @param null|string|Twig_Template $localTemplate a file where to look for the block first
     *
     * @return array
     */
    private function getBlocksByField( Content $content, Field $field, $type, $localTemplate = null )
    {
        $fieldBlockName = $this->getRenderFieldBlockName( $content, $field, $type );
        if ( $localTemplate !== null )
        {
            // $localTemplate might be a Twig_Template instance already (e.g. using _self Twig keyword)
            if ( !$localTemplate instanceof Twig_Template )
            {
                $localTemplate = $this->twig->loadTemplate( $localTemplate );
            }

            $block = $this->searchBlock( $fieldBlockName, $localTemplate );
            if ( $block !== null )
            {
                return [$fieldBlockName => $block];
            }
        }
        return $this->getBlockByName( $fieldBlockName, $type === self::EDIT ? 'fieldEditResources' : 'fieldViewResources' );
    }

    /**
     * Returns the template block for the settings of the field definition $definition.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $definition
     * @param int $type Either self::VIEW or self::EDIT
     *
     * @return array
     */
    private function getBlocksByFieldDefinition( FieldDefinition $definition, $type )
    {
        return $this->getBlockByName(
            $this->getRenderFieldDefinitionBlockName( $definition, $type ),
            $type === self::EDIT ? 'fieldDefinitionEditResources' : 'fieldDefinitionViewResources'
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
    private function getBlockByName( $name, $resourcesName )
    {
        if ( isset( $this->blocks[$name] ) )
        {
            return [$name => $this->blocks[$name]];
        }

        foreach ( $this->{$resourcesName} as &$template )
        {
            if ( !$template instanceof Twig_Template )
                $template = $this->twig->loadTemplate( $template['template'] );

            $tpl = $template;

            $block = $this->searchBlock( $name, $tpl );
            if ( $block !== null )
            {
                $this->blocks[$name] = $block;
                return [$name => $block];
            }
        }
        throw new LogicException( "Cannot find '$name' template block." );
    }

    /**
     * Returns expected block name for $field, attached in $content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param int $type Either self::VIEW or self::EDIT
     *
     * @return string
     */
    private function getRenderFieldBlockName( Content $content, Field $field, $type )
    {
        $suffix = $type === self::EDIT ? self::FIELD_EDIT_SUFFIX : self::FIELD_VIEW_SUFFIX;
        return $this->getFieldTypeIdentifier( $content, $field ) . $suffix;
    }

    /**
     * Returns the name of the block to render the settings of the field
     * definition $definition
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $definition
     * @param int $type Either self::VIEW or self::EDIT
     *
     * @return string
     */
    private function getRenderFieldDefinitionBlockName( FieldDefinition $definition, $type )
    {
        $suffix = $type === self::EDIT ? self::FIELD_DEFINITION_EDIT_SUFFIX : self::FIELD_DEFINITION_VIEW_SUFFIX;
        return $definition->fieldTypeIdentifier . $suffix;
    }

    /**
     * Returns the field type identifier for $field
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     *
     * @return string
     */
    private function getFieldTypeIdentifier( Content $content, Field $field )
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
