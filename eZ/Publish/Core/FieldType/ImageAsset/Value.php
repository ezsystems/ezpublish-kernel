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
     * The alternative image text (for example "Picture of an apple.").
     *
     * @var string|null
     */
    public $alternativeText;

    /**
     * @param mixed|null $destinationContentId
     * @param string|null $alternativeText
     */
    public function __construct($destinationContentId = null, ?string $alternativeText = null)
    {
        parent::__construct([
            'destinationContentId' => $destinationContentId,
            'alternativeText' => $alternativeText,
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
