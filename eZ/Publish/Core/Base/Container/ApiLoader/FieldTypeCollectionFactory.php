<?php

/**
 * File containing the FieldTypeCollectionFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Base\Container\ApiLoader;

use Symfony\Component\DependencyInjection\ContainerAware;

class FieldTypeCollectionFactory extends ContainerAware
{
    /**
     * Collection of fieldTypes, lazy loaded via a closure.
     *
     * @var \Closure[]
     */
    protected $fieldTypes = array();

    /**
     * List of identifiers for "concrete" FieldTypes (i.e. not using NullFieldType).
     *
     * @var array
     */
    private $concreteFieldTypesIdentifiers = array();

    /**
     * Registers an eZ Publish field type.
     * Field types are being registered as a closure so that they will be lazy loaded.
     *
     * @param string $fieldTypeServiceId The field type service Id
     * @param string $fieldTypeAlias The field type alias (e.g. "ezstring")
     */
    public function registerFieldType($fieldTypeServiceId, $fieldTypeAlias)
    {
        $container = $this->container;
        $this->fieldTypes[$fieldTypeAlias] = function () use ($container, $fieldTypeServiceId) {
            return $container->get($fieldTypeServiceId);
        };
    }

    /**
     * Returns registered field types (as closures to be lazy loaded in the public API).
     *
     * @return \Closure[]
     */
    public function getFieldTypes()
    {
        return $this->fieldTypes;
    }

    /**
     * Registers $fieldTypeIdentifier as "concrete" FieldType (i.e. not using NullFieldType).
     *
     * @param string $fieldTypeIdentifier
     */
    public function registerConcreteFieldTypeIdentifier($fieldTypeIdentifier)
    {
        $this->concreteFieldTypesIdentifiers[] = $fieldTypeIdentifier;
    }

    public function getConcreteFieldTypesIdentifiers()
    {
        return $this->concreteFieldTypesIdentifiers;
    }
}
