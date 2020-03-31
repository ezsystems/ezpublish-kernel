<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Tests\Persistence;

use PHPUnit\Runner\Exception;

/**
 * Abstract data fixture for file-based fixtures. Handles in-memory caching.
 *
 * @internal for internal use by Repository test setup
 */
abstract class BaseInMemoryCachedFileFixture implements Fixture
{
    /** @var array|null */
    private static $inMemoryCachedLoadedData = null;

    /** @var string */
    private $filePath;

    /**
     * Perform uncached load of data (always done only once).
     */
    abstract protected function loadFixture(): array;

    final public function getFilePath(): string
    {
        return $this->filePath;
    }

    final public function __construct(string $filePath)
    {
        $this->filePath = realpath($filePath);
        if (false === $this->filePath) {
            throw new Exception("The fixture file does not exist: {$filePath}");
        }
    }

    final public function load(): array
    {
        // avoid reading disc to load the same file multiple times
        if (!isset(self::$inMemoryCachedLoadedData[$this->filePath])) {
            self::$inMemoryCachedLoadedData[$this->filePath] = $this->loadFixture();
        }

        return self::$inMemoryCachedLoadedData[$this->filePath] ?? [];
    }
}
