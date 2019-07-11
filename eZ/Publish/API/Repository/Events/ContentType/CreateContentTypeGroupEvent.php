<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ContentType;

use eZ\Publish\API\Repository\Events\AfterEvent;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;

interface CreateContentTypeGroupEvent extends AfterEvent
{
    public function getReturnValue(): ContentTypeGroup;

    public function getContentTypeGroupCreateStruct(): ContentTypeGroupCreateStruct;
}
