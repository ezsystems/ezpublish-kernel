<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\Gateway\Content;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;

/**
 * @internal for internal use by Repository Filtering Handlers
 */
interface GatewayDataMapper
{
    /**
     * Map raw database data to SPI Persistence ContentItem ValueObject.
     */
    public function mapRawDataToPersistenceContentItem(array $row): Content\ContentItem;

    public function mapContentMetadataToPersistenceContentInfo(array $row): ContentInfo;
}
