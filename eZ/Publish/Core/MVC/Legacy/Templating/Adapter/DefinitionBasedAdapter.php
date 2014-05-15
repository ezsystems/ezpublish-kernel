<?php
/**
 * File containing the DefinitionBasedAdapter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Adapter;

use eZ\Publish\API\Repository\Values\ValueObject;

abstract class DefinitionBasedAdapter extends ValueObjectAdapter
{
    /**
     * {@inheritDoc}
     */
    public function __construct( ValueObject $valueObject )
    {
        parent::__construct( $valueObject, $this->definition() );
    }

    /**
     * Returns the hash map, mapping the legacy attributes name (key) to the value object property name (value)
     * (e.g. my_legacy_attribute_name => newPropertyName).
     *
     * The value of an entry in the returned array can also be a closure which would be called directly with the value object as only parameter.
     *
     * @return array
     */
    abstract protected function definition();
}
