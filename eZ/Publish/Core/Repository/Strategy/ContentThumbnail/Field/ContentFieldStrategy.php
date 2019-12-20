<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Strategy\ContentThumbnail\Field;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Thumbnail;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\Field\ThumbnailStrategy;

final class ContentFieldStrategy implements ThumbnailStrategy
{
    /** @var \eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\Field\ThumbnailStrategy[] */
    private $strategies = [];

    public function __construct(array $strategies = [])
    {
        $this->setStrategies($strategies);
    }

    /**
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function getThumbnail(Field $field): ?Thumbnail
    {
        if (!$this->hasStrategy($field->fieldTypeIdentifier)) {
            throw new NotFoundException('Field\ThumbnailStrategy', $field->fieldTypeIdentifier);
        }

        return $this->strategies[$field->fieldTypeIdentifier]->getThumbnail($field);
    }

    public function hasStrategy(string $fieldTypeIdentifier): bool
    {
        return isset($this->strategies[$fieldTypeIdentifier]);
    }

    public function addStrategy(string $fieldTypeIdentifier, ThumbnailStrategy $thumbnailStrategy): void
    {
        $this->strategies[$fieldTypeIdentifier] = $thumbnailStrategy;
    }

    public function setStrategies(array $thumbnailStrategies): void
    {
        foreach ($thumbnailStrategies as $fieldTypeIdentifier => $thumbnailStrategy) {
            $this->addStrategy($fieldTypeIdentifier, $thumbnailStrategy);
        }
    }
}
