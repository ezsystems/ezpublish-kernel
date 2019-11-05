<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\User;

use eZ\Publish\SPI\FieldType\Indexable;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Search;

final class SearchField implements Indexable
{
    public function getIndexData(Field $field, FieldDefinition $fieldDefinition): array
    {
        return [
            new Search\Field(
                'fulltext_email',
                $field->value->externalData['email'],
                new Search\FieldType\FullTextField()
            ),
        ];
    }

    public function getIndexDefinition(): array
    {
        return [
            'fulltext_email' => new Search\FieldType\FullTextField(),
        ];
    }

    public function getDefaultMatchField()
    {
        return 'fulltext_email';
    }

    public function getDefaultSortField()
    {
        return $this->getDefaultMatchField();
    }
}
