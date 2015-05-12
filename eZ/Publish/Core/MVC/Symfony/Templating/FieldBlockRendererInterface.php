<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * Interface for content fields/fieldDefinitions renderers.
 * Implementors can render view and edit views for fields/fieldDefinitions.
 */
interface FieldBlockRendererInterface
{
    /**
     * Renders the HTML view markup for a given field.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldIdentifier Identifier for the field we want to view
     * @param array $params An array of parameters to pass to the field view
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no field with provided $fieldIdentifier can be found in $content.
     * @return string
     */
    public function renderContentFieldView( Content $content, $fieldIdentifier, array $params = [] );

    /**
     * Renders the HTML edit markup for a given field.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldIdentifier Identifier for the field we want to edit
     * @param array $params An array of parameters to pass to the field edit view
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no field with provided $fieldIdentifier can be found in $content.
     * @return string
     */
    public function renderContentFieldEdit( Content $content, $fieldIdentifier, array $params = [] );

    /**
     * Renders the HTML view markup for the given field definition.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $definition
     * @return string
     */
    public function renderFieldDefinitionView( FieldDefinition $fieldDefinition, array $params = [] );

    /**
     * Renders the HTML edot markup for the given field definition.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $definition
     * @return string
     */
    public function renderFieldDefinitionEdit( FieldDefinition $fieldDefinition, array $params = [] );
}
