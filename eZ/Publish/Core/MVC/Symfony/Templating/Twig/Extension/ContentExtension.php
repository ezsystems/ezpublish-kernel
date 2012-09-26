<?php
/**
 * File containing the ContentExtension class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use \Twig_Extension;
use \Twig_Environment;
use \Twig_Function_Method;
use \Twig_Template;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use \SplObjectStorage;
use \InvalidArgumentException;
use \LogicException;

/**
 * Twig content extension for eZ Publish specific usage.
 * Exposes helpers to play with public API objects.
 */
class ContentExtension extends Twig_Extension
{
    /**
     * Array of Twig template resources.
     * Either path to each template is referenced or its \Twig_Template (compiled) counterpart
     *
     * @var string[]|\Twig_Template[]
     */
    protected $resources;

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
     * Template blocks, by field
     *
     * @var \SplObjectStorage
     */
    protected $blocks;

    /**
     * Hash of field type identifiers (i.e. "ezstring"), indexed by field definition identifier
     *
     * @var array
     */
    protected $fieldTypeIdentifiers = array();

    public function __construct( array $resources = array() )
    {
        $this->resources = $resources;
        $this->blocks = new SplObjectStorage();
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
        // Merging passed parameters to default ones
        $params += array(
            'lang' => null,
            'editMode' => false,
            'parameters' => array(), // parameters dedicated to template processing
            'attr' => array() // attributes to add on the enclosing HTML tags
        );

        $field = $content->getField( $fieldIdentifier, $params['lang'] );
        if ( !$field instanceof Field )
            throw new InvalidArgumentException( "Invalid field identifier '$fieldIdentifier' for content #{$content->contentInfo->id}" );

        $contentInfo = $content->getVersionInfo()->getContentInfo();
        $contentType = $contentInfo->getContentType();
        // Adding Field, FieldSettings and ContentInfo objects to
        // parameters to be passed to the template
        $params += array(
            'field' => $field,
            'contentInfo' => $contentInfo,
            'fieldSettings' => $contentType->getFieldDefinition( $fieldIdentifier )->getFieldSettings()
        );

        // Ensure that not edit metadata has been injected from the template
        unset( $params['editMeta'] );
        if ( $params['editMode'] ?: $this->isInEditMode() )
        {
            $params += array(
                'editMeta' => $this->getEditMetadata( $content, $field )
            );
        }

        // make we can easily add class="<fieldtypeidentifier>-field" to the
        // generated HTML
        if ( isset( $params['attr']['class'] ) )
        {
            $params['attr']['class'] .= $this->getFieldTypeIdentifier( $content, $field ) . '-field';
        }
        else
        {
            $params['attr']['class'] = $this->getFieldTypeIdentifier( $content, $field ) . '-field';
        }

        // Getting instance of Twig_Template that will be used to render blocks
        if ( !$this->template instanceof Twig_Template )
        {
            $this->template = $this->environment->loadTemplate( reset( $this->resources ) );
        }

        return $this->template->renderBlock(
            $this->getFieldBlockName( $content, $field ),
            $params,
            $this->getBlocksByField( $content, $field )
        );
    }

    /**
     * Returns template blocks for $field.
     * Template block convention name is <fieldTypeIdentifier>_field
     * Example: 'ezstring_field' will be relevant for a full view of ezstring field type
     *
     * @param Content $content
     * @param Field $field
     * @return array
     * @throws \LogicException If no template block can be found for $field
     */
    protected function getBlocksByField( Content $content, Field $field )
    {
        if ( $this->blocks->contains( $field ) )
            return $this->blocks[$field];

        // Looping against available resources to find template blocks for $field
        //TODO: maybe we should consider "themes" like in forms - http://symfony.com/doc/master/book/forms.html#form-theming
        $blocks = array();
        foreach ( $this->resources as &$template )
        {
            if ( !$template instanceof Twig_Template )
                $template = $this->environment->loadTemplate( $template );

            $tpl = $template;
            $fieldBlockName = $this->getFieldBlockName( $content, $field );

            // Current template might have parents, so we need to loop against them to find a matching block
            do
            {
                foreach ( $tpl->getBlocks() as $blockName => $block )
                {
                    if ( strpos( $blockName, $fieldBlockName ) === 0 )
                    {
                        $blocks[$blockName] = $block;
                    }
                }
            }
            while ( $tpl = $tpl->getParent( array() ) !== false );
        }

        if ( empty( $blocks ) )
            throw new LogicException( "Cannot find '$fieldBlockName' template block field type." );

        $this->blocks->attach( $field, $blocks );

        return $blocks;
    }

    /**
     * Returns expected block name for $field, attached in $content.
     *
     * @param \eZ\Publish\Core\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @return string
     */
    protected function getFieldBlockName( Content $content, Field $field )
    {
        return $this->getFieldTypeIdentifier( $content, $field ) . '_field';
    }

    /**
     * Returns the field type identifier for $field
     *
     * @param \eZ\Publish\Core\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @return string
     */
    protected function getFieldTypeIdentifier( Content $content, Field $field )
    {
        if ( !isset( $this->fieldTypeIdentifiers[$field->fieldDefIdentifier] ) )
        {
            $this->fieldTypeIdentifiers[$field->fieldDefIdentifier] = $content
                ->getVersionInfo()
                ->getContentInfo()
                ->getContentType()
                ->getFieldDefinition( $field->fieldDefIdentifier )
                ->fieldTypeIdentifier;
        }

        return $this->fieldTypeIdentifiers[$field->fieldDefIdentifier];
    }

    /**
     * Checks if we are in edit mode or not (editorial interface).
     *
     * @todo Needs to check in the session and via the API if current user has access to edit mode
     * @return bool
     */
    protected function isInEditMode()
    {
        return false;
    }

    /**
     * Returns metadata needed for edition while using the editorial interface.
     * These will basically be rendered as HTML data attributes, prefixed by "data-ez".
     * Example: data-ez-field-id="12345"
     *
     * @param \eZ\Publish\Core\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @return array
     * @todo It would make sense to also ask for additional metadata supported by the field type
     */
    protected function getEditMetadata( Content $content, Field $field )
    {
        $versionInfo = $content->getVersionInfo();

        return array(
            'field-id'                  => $field->id,
            'field-identifier'          => $field->fieldDefIdentifier,
            'field-type-identifier'     => $this->getFieldTypeIdentifier( $content, $field ),
            'content-id'                => $versionInfo->getContentInfo()->id,
            'version'                   => $versionInfo->versionNo,
            'locale-code'               => $field->languageCode
        );
    }
}
