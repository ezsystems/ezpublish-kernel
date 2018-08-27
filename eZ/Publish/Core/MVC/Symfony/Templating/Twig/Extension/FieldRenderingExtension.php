<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistryInterface;
use eZ\Publish\Core\MVC\Symfony\Templating\FieldBlockRendererInterface;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Twig extension for content fields/fieldDefinitions rendering (view and edit).
 */
class FieldRenderingExtension extends Twig_Extension
{
    /**
     * @var FieldBlockRendererInterface|\eZ\Publish\Core\MVC\Symfony\Templating\Twig\FieldBlockRenderer
     */
    private $fieldBlockRenderer;

    /**
     * @var ParameterProviderRegistryInterface
     */
    private $parameterProviderRegistry;

    /**
     * @var TranslationHelper
     */
    private $translationHelper;

    /**
     * Hash of field type identifiers (i.e. "ezstring"), indexed by field definition identifier.
     *
     * @var array
     */
    private $fieldTypeIdentifiers = [];

    public function __construct(
        FieldBlockRendererInterface $fieldBlockRenderer,
        ParameterProviderRegistryInterface $parameterProviderRegistry,
        TranslationHelper $translationHelper
    ) {
        $this->fieldBlockRenderer = $fieldBlockRenderer;
        $this->parameterProviderRegistry = $parameterProviderRegistry;
        $this->translationHelper = $translationHelper;
    }

    public function getName()
    {
        return 'ezpublish.field_rendering';
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction(
                'ez_render_field',
                function (Twig_Environment $environment, Content $content, $fieldIdentifier, array $params = []) {
                    $this->fieldBlockRenderer->setTwig($environment);

                    return $this->renderField($content, $fieldIdentifier, $params);
                },
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new Twig_SimpleFunction(
                'ez_render_fielddefinition_settings',
                function (Twig_Environment $environment, FieldDefinition $fieldDefinition, array $params = []) {
                    $this->fieldBlockRenderer->setTwig($environment);

                    return $this->renderFieldDefinitionSettings($fieldDefinition, $params);
                },
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        );
    }

    /**
     * Renders the HTML for the settings for the given field definition
     * $definition.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     *
     * @return string
     */
    public function renderFieldDefinitionSettings(FieldDefinition $fieldDefinition, array $params = [])
    {
        return $this->fieldBlockRenderer->renderFieldDefinitionView($fieldDefinition, $params);
    }

    /**
     * Renders the HTML for a given field.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldIdentifier Identifier for the field we want to render
     * @param array $params An array of parameters to pass to the field view
     *
     * @return string The HTML markup
     *
     * @throws InvalidArgumentException
     */
    public function renderField(Content $content, $fieldIdentifier, array $params = [])
    {
        $field = $this->translationHelper->getTranslatedField($content, $fieldIdentifier, isset($params['lang']) ? $params['lang'] : null);
        if (!$field instanceof Field) {
            throw new InvalidArgumentException(
                '$fieldIdentifier',
                "'{$fieldIdentifier}' field not present on content #{$content->contentInfo->id} '{$content->contentInfo->name}'"
            );
        }

        $params = $this->getRenderFieldBlockParameters($content, $field, $params);
        $fieldTypeIdentifier = $this->getFieldTypeIdentifier($content, $field);

        return $this->fieldBlockRenderer->renderContentFieldView($field, $fieldTypeIdentifier, $params);
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
    private function getRenderFieldBlockParameters(Content $content, Field $field, array $params = [])
    {
        // Merging passed parameters to default ones
        $params += [
            'parameters' => [], // parameters dedicated to template processing
            'attr' => [], // attributes to add on the enclosing HTML tags
        ];

        $versionInfo = $content->getVersionInfo();
        $contentInfo = $versionInfo->getContentInfo();
        $contentType = $content->getContentType();
        $fieldDefinition = $contentType->getFieldDefinition($field->fieldDefIdentifier);
        // Adding Field, FieldSettings and ContentInfo objects to
        // parameters to be passed to the template
        $params += [
            'field' => $field,
            'content' => $content,
            'contentInfo' => $contentInfo,
            'versionInfo' => $versionInfo,
            'fieldSettings' => $fieldDefinition->getFieldSettings(),
        ];

        // Adding field type specific parameters if any.
        if ($this->parameterProviderRegistry->hasParameterProvider($fieldDefinition->fieldTypeIdentifier)) {
            $params['parameters'] += $this->parameterProviderRegistry
                ->getParameterProvider($fieldDefinition->fieldTypeIdentifier)
                ->getViewParameters($field);
        }

        // make sure we can easily add class="<fieldtypeidentifier>-field" to the
        // generated HTML
        if (isset($params['attr']['class'])) {
            $params['attr']['class'] .= ' ' . $this->getFieldTypeIdentifier($content, $field) . '-field';
        } else {
            $params['attr']['class'] = $this->getFieldTypeIdentifier($content, $field) . '-field';
        }

        return $params;
    }

    /**
     * Returns the field type identifier for $field.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     *
     * @return string
     */
    private function getFieldTypeIdentifier(Content $content, Field $field)
    {
        $contentType = $content->getContentType();
        $key = $contentType->id . '  ' . $field->fieldDefIdentifier;

        if (!isset($this->fieldTypeIdentifiers[$key])) {
            $this->fieldTypeIdentifiers[$key] = $contentType
                ->getFieldDefinition($field->fieldDefIdentifier)
                ->fieldTypeIdentifier;
        }

        return $this->fieldTypeIdentifiers[$key];
    }
}
