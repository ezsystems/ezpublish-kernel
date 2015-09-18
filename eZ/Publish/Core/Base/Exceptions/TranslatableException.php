<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Base\Exceptions;

/**
 * Trait providing a default implementation of TranslatableExceptionInterface.
 */
trait TranslatableException
{
    private $messageTemplate;

    private $parameters = [];

    /**
     * @param string $messageTemplate
     */
    public function setMessageTemplate($messageTemplate)
    {
        $this->messageTemplate = $messageTemplate;
    }

    /**
     * Returns the message template, with placeholders for parameters.
     * E.g. "Content with ID %contentId% could not be found".
     *
     * @return string
     */
    public function getMessageTemplate()
    {
        return $this->messageTemplate;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function addParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @param array $parameters
     */
    public function addParameters(array $parameters)
    {
        $this->parameters += $parameters;
    }

    /**
     * Returns a hash map with param placeholder as key and its corresponding value.
     * E.g. array('%contentId%' => 123).
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns base translation, as stored in $message property.
     *
     * @return string
     */
    public function getBaseTranslation()
    {
        return strtr($this->messageTemplate, $this->parameters);
    }
}
