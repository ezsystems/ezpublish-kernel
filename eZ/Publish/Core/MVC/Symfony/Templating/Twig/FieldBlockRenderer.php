<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\MVC\Symfony\Templating\Exception\MissingFieldBlockException;
use eZ\Publish\Core\MVC\Symfony\Templating\FieldBlockRendererInterface;
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

    /** @var Twig_Environment */
    private $twig;

    /**
     * Array of Twig template resources for field view.
     * Either the path to each template and its priority in a hash or its
     * \Twig_Template (compiled) counterpart.
     *
     * @var Twig_Template[]|array
     */
    private $fieldViewResources = [];

    /**
     * Array of Twig template resources for field edit.
     * Either the path to each template and its priority in a hash or its
     * \Twig_Template (compiled) counterpart.
     *
     * @var Twig_Template[]|array
     */
    private $fieldEditResources = [];

    /**
     * Array of Twig template resources for field definition view.
     * Either the path to each template and its priority in a hash or its
     * \Twig_Template (compiled) counterpart.
     *
     * @var Twig_Template[]|array
     */
    private $fieldDefinitionViewResources = [];

    /**
     * Array of Twig template resources for field definition edit.
     * Either the path to each template and its priority in a hash or its
     * \Twig_Template (compiled) counterpart.
     *
     * @var Twig_Template[]|array
     */
    private $fieldDefinitionEditResources = [];

    /**
     * A \Twig_Template instance used to render template blocks, or path to the template to use.
     *
     * @var Twig_Template|string
     */
    private $baseTemplate;

    /**
     * Template blocks.
     *
     * @var array
     */
    private $blocks = [];

    /**
     * @param Twig_Environment $twig
     */
    public function setTwig(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param string|Twig_Template $baseTemplate
     */
    public function setBaseTemplate($baseTemplate)
    {
        $this->baseTemplate = $baseTemplate;
    }

    /**
     * @param array $fieldViewResources
     */
    public function setFieldViewResources(array $fieldViewResources = null)
    {
        $this->fieldViewResources = (array)$fieldViewResources;
        usort($this->fieldViewResources, [$this, 'sortResourcesCallback']);
    }

    /**
     * @param array $fieldEditResources
     */
    public function setFieldEditResources(array $fieldEditResources = null)
    {
        $this->fieldEditResources = (array)$fieldEditResources;
        usort($this->fieldEditResources, [$this, 'sortResourcesCallback']);
    }

    /**
     * @param array $fieldDefinitionViewResources
     */
    public function setFieldDefinitionViewResources(array $fieldDefinitionViewResources = null)
    {
        $this->fieldDefinitionViewResources = (array)$fieldDefinitionViewResources;
        usort($this->fieldDefinitionViewResources, [$this, 'sortResourcesCallback']);
    }

    /**
     * @param array $fieldDefinitionEditResources
     */
    public function setFieldDefinitionEditResources(array $fieldDefinitionEditResources = null)
    {
        $this->fieldDefinitionEditResources = (array)$fieldDefinitionEditResources;
        usort($this->fieldDefinitionEditResources, [$this, 'sortResourcesCallback']);
    }

    public function sortResourcesCallback(array $a, array $b)
    {
        return $b['priority'] - $a['priority'];
    }

    public function renderContentFieldView(Field $field, $fieldTypeIdentifier, array $params = [])
    {
        return $this->renderContentField($field, $fieldTypeIdentifier, $params, self::VIEW);
    }

    public function renderContentFieldEdit(Field $field, $fieldTypeIdentifier, array $params = [])
    {
        return $this->renderContentField($field, $fieldTypeIdentifier, $params, self::EDIT);
    }

    /**
     * @param Field $field
     * @param string $fieldTypeIdentifier
     * @param array $params
     * @param int $type Either self::VIEW or self::EDIT
     *
     * @throws MissingFieldBlockException If no template block can be found for $field
     *
     * @return string
     */
    private function renderContentField(Field $field, $fieldTypeIdentifier, array $params, $type)
    {
        $localTemplate = null;
        if (isset($params['template'])) {
            // local override of the template
            // this template is put on the top the templates stack
            $localTemplate = $params['template'];
            unset($params['template']);
        }

        $params += ['field' => $field];

        // Getting instance of Twig_Template that will be used to render blocks
        if (is_string($this->baseTemplate)) {
            $this->baseTemplate = $this->twig->loadTemplate($this->baseTemplate);
        }
        $blockName = $this->getRenderFieldBlockName($fieldTypeIdentifier, $type);
        $context = $this->twig->mergeGlobals($params);
        $blocks = $this->getBlocksByField($fieldTypeIdentifier, $type, $localTemplate);

        if (!$this->baseTemplate->hasBlock($blockName, $context, $blocks)) {
            throw new MissingFieldBlockException("Cannot find '$blockName' template block.");
        }

        return $this->baseTemplate->renderBlock($blockName, $context, $blocks);
    }

    public function renderFieldDefinitionView(FieldDefinition $fieldDefinition, array $params = [])
    {
        return $this->renderFieldDefinition($fieldDefinition, $params, self::VIEW);
    }

    public function renderFieldDefinitionEdit(FieldDefinition $fieldDefinition, array $params = [])
    {
        return $this->renderFieldDefinition($fieldDefinition, $params, self::EDIT);
    }

    /**
     * @param FieldDefinition $fieldDefinition
     * @param array $params
     * @param int $type Either self::VIEW or self::EDIT
     *
     * @return string
     */
    private function renderFieldDefinition(FieldDefinition $fieldDefinition, array $params, $type)
    {
        if (is_string($this->baseTemplate)) {
            $this->baseTemplate = $this->twig->loadTemplate($this->baseTemplate);
        }

        $params += [
            'fielddefinition' => $fieldDefinition,
            'settings' => $fieldDefinition->getFieldSettings(),
        ];
        $blockName = $this->getRenderFieldDefinitionBlockName($fieldDefinition->fieldTypeIdentifier, $type);
        $context = $this->twig->mergeGlobals($params);
        $blocks = $this->getBlocksByFieldDefinition($fieldDefinition, $type);

        if (!$this->baseTemplate->hasBlock($blockName, $context, $blocks)) {
            return '';
        }

        return $this->baseTemplate->renderBlock($blockName, $context, $blocks);
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
    private function searchBlock($blockName, Twig_Template $tpl)
    {
        // Current template might have parents, so we need to loop against
        // them to find a matching block
        do {
            foreach ($tpl->getBlocks() as $name => $block) {
                if ($name === $blockName) {
                    return $block;
                }
            }
        } while (($tpl = $tpl->getParent([])) instanceof Twig_Template);

        return null;
    }

    /**
     * Returns template blocks for $fieldTypeIdentifier. First check in the $localTemplate if it's provided.
     * Template block convention name is <fieldTypeIdentifier>_field
     * Example: 'ezstring_field' will be relevant for a full view of ezstring field type.
     *
     * @param string $fieldTypeIdentifier
     * @param int $type Either self::VIEW or self::EDIT
     * @param null|string|Twig_Template $localTemplate a file where to look for the block first
     *
     * @return array
     */
    private function getBlocksByField($fieldTypeIdentifier, $type, $localTemplate = null)
    {
        $fieldBlockName = $this->getRenderFieldBlockName($fieldTypeIdentifier, $type);
        if ($localTemplate !== null) {
            // $localTemplate might be a Twig_Template instance already (e.g. using _self Twig keyword)
            if (!$localTemplate instanceof Twig_Template) {
                $localTemplate = $this->twig->loadTemplate($localTemplate);
            }

            $block = $this->searchBlock($fieldBlockName, $localTemplate);
            if ($block !== null) {
                return [$fieldBlockName => $block];
            }
        }

        return $this->getBlockByName($fieldBlockName, $type === self::EDIT ? 'fieldEditResources' : 'fieldViewResources');
    }

    /**
     * Returns the template block for the settings of the field definition $definition.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $definition
     * @param int $type Either self::VIEW or self::EDIT
     *
     * @return array
     */
    private function getBlocksByFieldDefinition(FieldDefinition $definition, $type)
    {
        return $this->getBlockByName(
            $this->getRenderFieldDefinitionBlockName($definition->fieldTypeIdentifier, $type),
            $type === self::EDIT ? 'fieldDefinitionEditResources' : 'fieldDefinitionViewResources'
        );
    }

    /**
     * Returns the template block of the given $name available in the resources
     * which name is $resourcesName.
     *
     * @param string $name
     * @param string $resourcesName
     *
     * @return array
     */
    private function getBlockByName($name, $resourcesName)
    {
        if (isset($this->blocks[$name])) {
            return [$name => $this->blocks[$name]];
        }

        foreach ($this->{$resourcesName} as &$template) {
            if (!$template instanceof Twig_Template) {
                $template = $this->twig->loadTemplate($template['template']);
            }

            $tpl = $template;

            $block = $this->searchBlock($name, $tpl);
            if ($block !== null) {
                $this->blocks[$name] = $block;

                return [$name => $block];
            }
        }

        return [];
    }

    /**
     * Returns expected block name for $fieldTypeIdentifier, attached in $content.
     *
     * @param string $fieldTypeIdentifier
     * @param int $type Either self::VIEW or self::EDIT
     *
     * @return string
     */
    private function getRenderFieldBlockName($fieldTypeIdentifier, $type)
    {
        $suffix = $type === self::EDIT ? self::FIELD_EDIT_SUFFIX : self::FIELD_VIEW_SUFFIX;

        return $fieldTypeIdentifier . $suffix;
    }

    /**
     * Returns the name of the block to render the settings of the field
     * definition $definition.
     *
     * @param string $fieldTypeIdentifier
     * @param int $type Either self::VIEW or self::EDIT
     *
     * @return string
     */
    private function getRenderFieldDefinitionBlockName($fieldTypeIdentifier, $type)
    {
        $suffix = $type === self::EDIT ? self::FIELD_DEFINITION_EDIT_SUFFIX : self::FIELD_DEFINITION_VIEW_SUFFIX;

        return $fieldTypeIdentifier . $suffix;
    }
}
