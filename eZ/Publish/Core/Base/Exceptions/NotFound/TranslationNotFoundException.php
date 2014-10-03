<?php
/**
 * This file is part of the eZ Publish package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\Base\Exceptions\NotFound;

use eZ\Publish\Core\Base\Exceptions\Httpable;
use Mockery\Exception\RuntimeException;
use Exception;

class TranslationNotFoundException extends RuntimeException implements Httpable
{
    /**
     * Creates a FieldType Not Found exception with info on how to fix
     *
     * @param int $contentId
     * @param array $languageCodes
     * @param \Exception|null $previous
     */
    public function __construct( $contentId, array $languageCodes, Exception $previous = null )
    {
        $langList = implode( ' or ', $languageCodes );
        parent::__construct(
            "Content '{$contentId}' is not translated in '{$langList}', nor is it flagged as always available",
            self::NOT_FOUND,
            $previous
        );
    }
}
