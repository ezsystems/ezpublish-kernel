<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search;

/**
 * The Search Content translation handler.
 */
interface ContentTranslationHandler
{
    /**
     * Deletes a translation content object from the index.
     *
     * @param int $contentId
     * @param string $languageCode
     */
    public function deleteTranslation($contentId, $languageCode);
}
