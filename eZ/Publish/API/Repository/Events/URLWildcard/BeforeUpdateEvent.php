<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\URLWildcard;

use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\API\Repository\Values\Content\URLWildcardUpdateStruct;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;

final class BeforeUpdateEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\URLWildcard */
    private $urlWildcard;

    /** @var \eZ\Publish\API\Repository\Values\Content\URLWildcardUpdateStruct */
    private $updateStruct;

    public function __construct(
        URLWildcard $urlWildcard,
        URLWildcardUpdateStruct $updateStruct
    ) {
        $this->urlWildcard = $urlWildcard;
        $this->updateStruct = $updateStruct;
    }

    public function getUrlWildcard(): URLWildcard
    {
        return $this->urlWildcard;
    }

    public function getUpdateStruct(): URLWildcardUpdateStruct
    {
        return $this->updateStruct;
    }
}
