<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler;

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
    public function getFieldTypeServiceIds(ContainerBuilder $container): array
    {
        // Field types.
        // Alias attribute is the field type string.
        $deprecatedFieldTypeTags = $container->findTaggedServiceIds(self::DEPRECATED_FIELD_TYPE_SERVICE_TAG);
        foreach ($deprecatedFieldTypeTags as $deprecatedFieldTypeTag) {
            @trigger_error(
                sprintf(
                    '`%s` service tag is deprecated and will be removed in eZ Platform 4.0. Please use `%s`. instead.',
                    self::DEPRECATED_FIELD_TYPE_SERVICE_TAG,
                    self::FIELD_TYPE_SERVICE_TAG
                ),
                E_USER_DEPRECATED
            );
        }
        $fieldTypeTags = $container->findTaggedServiceIds(self::FIELD_TYPE_SERVICE_TAG);
        $fieldTypesTags = array_merge($deprecatedFieldTypeTags, $fieldTypeTags);
        foreach ($fieldTypesTags as $id => $attributes) {
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

        return $fieldTypesTags;
    }

    abstract public function process(ContainerBuilder $container);
}
