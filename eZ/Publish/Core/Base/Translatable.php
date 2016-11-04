<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base;

/**
 * Interface for translatable value objects.
 */
interface Translatable
{
    /**
     * Returns the message template, with placeholders for parameters.
     * E.g. "Content with ID %contentId% could not be found".
     *
     * @return string
     */
    public function getMessageTemplate();

    /**
     * Injects the message template.
     *
     * @param string $messageTemplate
     */
    public function setMessageTemplate($messageTemplate);

    /**
     * Returns a hash map with param placeholder as key and its corresponding value.
     * E.g. array('%contentId%' => 123).
     *
     * @return array
     */
    public function getParameters();

    /**
     * Injects the hash map, with param placeholder as key and its corresponding value.
     * E.g. array('%contentId%' => 123).
     * If parameters already existed, they will be replaced by the passed here.
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters);

    /**
     * Adds a parameter to existing hash map.
     *
     * @param string $name
     * @param string $value
     */
    public function addParameter($name, $value);

    /**
     * Adds $parameters to existing hash map.
     *
     * @param array $parameters
     */
    public function addParameters(array $parameters);

    /**
     * Returns base translation, computed with message template and parameters.
     *
     * @return string
     */
    public function getBaseTranslation();
}
