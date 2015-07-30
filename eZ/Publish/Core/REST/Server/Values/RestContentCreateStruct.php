<?php

/**
 * File containing the RestContentCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * RestContentCreateStruct view model.
 */
class RestContentCreateStruct extends RestValue
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct
     */
    public $contentCreateStruct;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct
     */
    public $locationCreateStruct;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct $contentCreateStruct
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct $locationCreateStruct
     */
    public function __construct(ContentCreateStruct $contentCreateStruct, LocationCreateStruct $locationCreateStruct)
    {
        $this->contentCreateStruct = $contentCreateStruct;
        $this->locationCreateStruct = $locationCreateStruct;
    }
}
