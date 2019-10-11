<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Compare;

use eZ\Publish\API\Repository\CompareEngine;
use OutOfBoundsException;

final class CompareEngineRegistry
{
    /** @var \eZ\Publish\API\Repository\CompareEngine[] */
    private $engines = [];

    /**
     * @param \eZ\Publish\API\Repository\CompareEngine[] $engines
     */
    public function __construct(array $engines = [])
    {
        foreach ($engines as $supportedType => $engine) {
            $this->registerEngine($supportedType, $engine);
        }
    }

    public function registerEngine(string $supportedType, CompareEngine $engine): void
    {
        $this->engines[$supportedType] = $engine;
    }

    public function getEngine(string $supportedType): CompareEngine
    {
        if (!isset($this->engines[$supportedType])) {
            throw new OutOfBoundsException(
                sprintf(
                    'There is no compare engine for type "%s".',
                    $supportedType,
                )
            );
        }

        return $this->engines[$supportedType];
    }
}
