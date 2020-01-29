<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\FieldRelation;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RelatedToContentQueryType extends AbstractQueryType
{
    public static function getName(): string
    {
        return 'eZ:RelatedToContent';
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['content']);
        $resolver->setAllowedTypes('content', [Content::class, ContentInfo::class, 'int']);
        $resolver->setNormalizer('content', function (Options $options, $value) {
            if ($value instanceof Content || $value instanceof ContentInfo) {
                $value = $value->id;
            }

            return $value;
        });

        $resolver->setRequired(['field']);
        $resolver->setAllowedTypes('field', ['string', Field::class]);
        $resolver->setNormalizer('field', static function (Options $options, $value) {
            if ($value instanceof Field) {
                $value = $value->fieldDefIdentifier;
            }

            return $value;
        });
    }

    protected function getQueryFilter(array $parameters): Criterion
    {
        return new FieldRelation(
            $parameters['field'],
            Criterion\Operator::CONTAINS,
            $parameters['content']
        );
    }
}
