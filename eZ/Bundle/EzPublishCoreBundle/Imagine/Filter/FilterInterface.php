<?php

/**
 * File containing the FilterInterface class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter;

use Imagine\Filter\FilterInterface as BaseFilterInterface;

interface FilterInterface extends BaseFilterInterface
{
    /**
     * Sets $value for $optionName.
     *
     * @param string $optionName
     * @param mixed $value
     */
    public function setOption($optionName, $value);

    /**
     * Returns value for $optionName.
     * Defaults to $defaultValue if $optionName doesn't exist.
     *
     * @param string $optionName
     * @param null|mixed $defaultValue
     *
     * @return mixed
     */
    public function getOption($optionName, $defaultValue = null);

    /**
     * Checks if $optionName exists and has a value.
     *
     * @param string $optionName
     *
     * @return bool
     */
    public function hasOption($optionName);

    /**
     * Replaces inner options by $options.
     *
     * @param array $options
     */
    public function setOptions(array $options);

    /**
     * Returns all options.
     *
     * @return array
     */
    public function getOptions();
}
