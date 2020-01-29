<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractLocationQueryType extends AbstractQueryType
{
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'location' => null,
            'content' => null,
        ]);

        $resolver->setAllowedTypes('location', ['null', 'int', Location::class]);
        $resolver->setNormalizer(
            'location',
            function (Options $options, $value): ?Location {
                if (is_int($value)) {
                    return $this->repository->getLocationService()->loadLocation($value);
                }

                return $value;
            }
        );

        $resolver->setAllowedTypes('content', ['null', 'int', Content::class, ContentInfo::class]);
        $resolver->setNormalizer(
            'content',
            function (Options $options, $value): ?ContentInfo {
                if (is_int($value)) {
                    return $this->repository->getContentService()->loadContentInfo($value);
                }

                if ($value instanceof Content) {
                    return $value->contentInfo;
                }

                return $value;
            }
        );
    }

    protected function resolveLocation(array $parameters): ?Location
    {
        $location = $parameters['location'];

        if ($location === null) {
            $content = $parameters['content'];

            if ($content instanceof ContentInfo) {
                $location = $content->getMainLocation();
            }
        }

        return $location;
    }
}
