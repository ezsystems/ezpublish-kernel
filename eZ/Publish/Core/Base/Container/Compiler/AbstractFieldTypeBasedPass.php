<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler;

use eZ\Publish\Core\Base\Container\Compiler\TaggedServiceIdsIterator\BackwardCompatibleIterator;
use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractFieldTypeBasedPass implements CompilerPassInterface
{
    public const FIELD_TYPE_SERVICE_TAG = 'ezplatform.field_type';
    public const DEPRECATED_FIELD_TYPE_SERVICE_TAG = 'ezpublish.fieldType';

    public const FIELD_TYPE_SERVICE_TAGS = [
        self::FIELD_TYPE_SERVICE_TAG,
        self::DEPRECATED_FIELD_TYPE_SERVICE_TAG,
    ];

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return array
     *
     * @throws \LogicException
     */
    public function getFieldTypeServiceIds(ContainerBuilder $container): iterable
    {
        $fieldTypesIterator = new BackwardCompatibleIterator(
            $container,
            self::FIELD_TYPE_SERVICE_TAG,
            self::DEPRECATED_FIELD_TYPE_SERVICE_TAG
        );

        foreach ($fieldTypesIterator as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        sprintf(
                            'The %s or %s service tag needs an "alias" attribute to identify the Field Type.',
                            self::DEPRECATED_FIELD_TYPE_SERVICE_TAG,
                            self::FIELD_TYPE_SERVICE_TAG
                        )
                    );
                }
            }
        }

        return $fieldTypesIterator;
    }

    abstract public function process(ContainerBuilder $container);
}
