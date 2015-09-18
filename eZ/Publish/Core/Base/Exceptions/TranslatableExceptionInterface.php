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
 * Interface for translatable exceptions.
 */
interface TranslatableExceptionInterface
{
    /**
     * Returns the message template, with placeholders for parameters.
     * E.g. "Content with ID %contentId% could not be found".
     *
     * @return string
     */
    public function getMessageTemplate();

    /**
     * Returns a hash map with param placeholder as key and its corresponding value.
     * E.g. array('%contentId%' => 123).
     *
     * @return array
     */
    public function getParameters();
}
