<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\Random;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\AbstractRandom;

class MySqlRandom extends AbstractRandom
{
    public function getDriverName(): string
    {
        return 'mysql';
    }

    public function getRandomFunctionName(?int $seed): string
    {
        return 'RAND(' . $seed . ')';
    }
}
