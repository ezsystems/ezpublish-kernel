<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\URL;

use eZ\Publish\API\Repository\Events\AfterEvent;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;

interface UpdateUrlEvent extends AfterEvent
{
    public function getUrl(): URL;

    public function getStruct(): URLUpdateStruct;

    public function getUpdatedUrl(): URL;
}
