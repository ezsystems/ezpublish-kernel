<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\ImageAsset;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    /**
     * Related content id's.
     *
     * @var mixed|null
     */
    public $destinationContentId;

    /**
     * @param mixed|null $destinationContentId
     */
    public function __construct($destinationContentId = null)
    {
        parent::__construct([
            'destinationContentId' => $destinationContentId,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->destinationContentId;
    }
}
