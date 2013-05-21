<?php
/**
 * File containing the BinaryContent controller class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\SPI\Variation\VariationHandler;

/**
 * Binary content controller
 */
class BinaryContent extends RestController
{
    /**
     * @var \eZ\Publish\SPI\Variation\VariationHandler
     */
    protected $imageVariationHandler;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\SPI\Variation\VariationHandler $imageVariationHandler
     */
    public function __construct( VariationHandler $imageVariationHandler )
    {
        $this->imageVariationHandler = $imageVariationHandler;
    }

    /**
     * Returns data about the image variation $variationIdentifier of image field $fieldId.
     * Will generate the alias if it hasn't been generated yet.
     *
     * @param int $fieldId
     * @param string $variationIdentifier
     * @throws NotFoundException if the content or image field aren't found
     */
    public function getImageVariation()
    {
        $urlArguments = $this->urlHandler->parse( 'getImageVariation', $this->request->path );

        $idArray = explode( '-', $urlArguments['imageId'] );
        if ( count( $idArray ) != 2 )
        {
            throw new Exceptions\NotFoundException( "Invalid image ID {$urlArguments['imageId']}" );
        }
        list( $contentId, $fieldId ) = $idArray;
        $variationIdentifier = $urlArguments['variationIdentifier'];

        $content = $this->repository->getContentService()->loadContent( $contentId );

        $fieldFound = false;
        /** @var $field \eZ\Publish\API\Repository\Values\Content\Field */
        foreach( $content->getFields() as $field )
        {
            if ( $field->id == $fieldId )
            {
                $fieldFound = true;
                break;
            }
        }

        if ( !$fieldFound )
        {
            throw new Exceptions\NotFoundException( "No image field with ID $fieldId could be found" );
        }

        if ( !isset( $this->imageVariationService ) )
            $this->imageVariationService = $this->container->get( 'ezpublish.fieldType.ezimage.variation_service' );

        $versionInfo = $this->repository->getContentService()->loadVersionInfo( $content->contentInfo );

        try
        {
            return $this->imageVariationHandler->getVariation(
                $field, $versionInfo, $variationIdentifier
            );
        }
        catch ( InvalidVariationException $e )
        {
            throw new Exceptions\NotFoundException( "Invalid image variation $variationIdentifier", 0, $e );
        }

        return $variation;
    }
}
