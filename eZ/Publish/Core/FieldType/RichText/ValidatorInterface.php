<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\RichText;

use DOMDocument;

interface ValidatorInterface
{
    /**
     * Validate the given $xmlDocument and returns list of errors.
     *
     * @param \DOMDocument $xmlDocument
     *
     * @return string[]
     */
    public function validateDocument(DOMDocument $xmlDocument): array;
}
