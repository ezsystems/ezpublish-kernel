<?php

/**
 * File containing the BinaryContent controller class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Controller as RestController;
use eZ\Publish\Core\REST\Server\Values\CachedValue;
use eZ\Publish\SPI\Variation\VariationHandler;
use eZ\Publish\API\Repository\Exceptions\InvalidVariationException;

/**
 * Binary content controller.
 */
class BinaryContent extends RestController
{
    /**
     * Construct controller.
     *
     * @param \eZ\Publish\SPI\Variation\VariationHandler $imageVariationHandler
     */
    public function __construct(VariationHandler $imageVariationHandler)
    {
        $this->imageVariationHandler = $imageVariationHandler;
    }

    /**
     * Returns data about the image variation $variationIdentifier of image field $fieldId.
     * Will generate the alias if it hasn't been generated yet.
     *
     * @param mixed  $imageId
     * @param string $variationIdentifier
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\SPI\Variation\Values\Variation
     */
    public function getImageVariation($imageId, $variationIdentifier)
    {
        $idArray = explode('-', $imageId);
        if (count($idArray) != 2) {
            throw new Exceptions\NotFoundException("Invalid image ID {$imageId}");
        }
        list($contentId, $fieldId) = $idArray;

        $content = $this->repository->getContentService()->loadContent($contentId);

        $fieldFound = false;
        /** @var $field \eZ\Publish\API\Repository\Values\Content\Field */
        foreach ($content->getFields() as $field) {
            if ($field->id == $fieldId) {
                $fieldFound = true;
                break;
            }
        }

        if (!$fieldFound) {
            throw new Exceptions\NotFoundException("No image field with ID $fieldId could be found");
        }

        // check the field's value
        if ($field->value->uri === null) {
            throw new Exceptions\NotFoundException("Image file {$field->value->id} doesn't exist");
        }

        $versionInfo = $this->repository->getContentService()->loadVersionInfo($content->contentInfo);

        try {
            $variation = $this->imageVariationHandler->getVariation($field, $versionInfo, $variationIdentifier);

            if ($content->contentInfo->mainLocationId === null) {
                return $variation;
            } else {
                return new CachedValue(
                    $variation,
                    array('locationId' => $content->contentInfo->mainLocationId)
                );
            }
        } catch (InvalidVariationException $e) {
            throw new Exceptions\NotFoundException("Invalid image variation $variationIdentifier");
        }
    }

    /**
     * @var \eZ\Publish\SPI\Variation\VariationHandler
     */
    protected $imageVariationHandler;
}
