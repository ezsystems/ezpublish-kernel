<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URLAlias;

use eZ\Publish\Core\Event\AfterEvent;

final class RemoveAliasesEvent extends AfterEvent
{
    /** @var array */
    private $aliasList;

    public function __construct(
        array $aliasList
    ) {
        $this->aliasList = $aliasList;
    }

    public function getAliasList(): array
    {
        return $this->aliasList;
    }
}
