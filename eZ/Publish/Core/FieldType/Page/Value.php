<?php

/**
 * File containing the Page Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Page;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\Core\FieldType\Page\Parts\Page as Page;

class Value extends BaseValue
{
    /**
     * Container for page definition.
     *
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Page
     */
    public $page;

    /**
     * Construct a new Value object.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Page $page
     */
    public function __construct(Page $page = null)
    {
        $this->page = $page;
    }

    /**
     * Returns a string representation of the field value.
     * This string representation must be compatible with format accepted via
     * {@link \eZ\Publish\SPI\FieldType\FieldType::buildValue}.
     *
     * @return string
     */
    public function __toString()
    {
        return '';
    }
}
