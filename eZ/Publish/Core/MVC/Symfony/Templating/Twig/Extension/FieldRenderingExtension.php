<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
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

    public function __construct(FieldBlockRendererInterface $fieldBlockRenderer )
    {
        $this->fieldBlockRenderer = $fieldBlockRenderer;
    }

    public function getName()
    {
        return 'ezpublish.field_rendering';
    }

    public function initRuntime( Twig_Environment $environment )
    {
        $this->fieldBlockRenderer->setTwig( $environment );
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction(
                'ez_render_field',
                [$this, 'renderField'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFunction(
                'ez_render_fielddefinition_settings',
                [$this, 'renderFieldDefinitionSettings'],
                ['is_safe' => ['html']]
            ),
        );
    }

    /**
     * Renders the HTML for the settings for the given field definition
     * $definition
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     *
     * @return string
     */
    public function renderFieldDefinitionSettings( FieldDefinition $fieldDefinition, array $params = [] )
    {
        return $this->fieldBlockRenderer->renderFieldDefinitionView( $fieldDefinition, $params );
    }

    /**
     * Renders the HTML for a given field.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldIdentifier Identifier for the field we want to render
     * @param array $params An array of parameters to pass to the field view
     *
     * @return string The HTML markup
     */
    public function renderField( Content $content, $fieldIdentifier, array $params = [] )
    {
        return $this->fieldBlockRenderer->renderContentFieldView( $content, $fieldIdentifier, $params );
    }
}
