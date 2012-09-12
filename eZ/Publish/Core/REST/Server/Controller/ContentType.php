<?php
/**
 * File containing the Content controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Server\Values;

use \eZ\Publish\API\Repository\ContentTypeService;

use Qafoo\RMF;

/**
 * ContentType controller
 */
class ContentType
{
    /**
     * Input dispatcher
     *
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    protected $inputDispatcher;

    /**
     * URL handler
     *
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    protected $urlHandler;

    /**
     * Content type service
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeServcie;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeServcie
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler, ContentTypeService $contentTypeServcie )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler = $urlHandler;
        $this->contentTypeServcie = $contentTypeServcie;
    }

    /**
     * Load a content info by remote ID
     *
     * @param RMF\Request $request
     * @return Content
     */
    public function loadContentType( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'type', $request->path );

        return $this->contentTypeServcie->loadContentType( $urlValues['type'] );
    }

    /**
     * Loads FieldDefinitions for a given type
     *
     * @param RMF\Request $request
     * @return FieldDefinitionList
     */
    public function loadFieldDefinitionList( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'typeFieldDefinitions', $request->path );

        $contentType = $this->contentTypeServcie->loadContentType( $urlValues['type'] );

        return new Values\FieldDefinitionList(
            $contentType,
            $contentType->getFieldDefinitions()
        );
    }

    /**
     * Loads a single FieldDefinition
     *
     * @param RMF\Request $request
     * @return FieldDefinition
     */
    public function loadFieldDefinition( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'typeFieldDefinition', $request->path );

        $contentType = $this->contentTypeServcie->loadContentType( $urlValues['type'] );

        foreach ( $contentType->getFieldDefinitions() as $fieldDefinition )
        {
            if ( $fieldDefinition->id == $urlValues['fieldDefinition'] )
            {
                return new Values\RestFieldDefinition(
                    $contentType,
                    $fieldDefinition
                );
            }
        }

        throw new Exceptions\NotFound( "Field definition not found: '{$request->path}'." );
    }
}
