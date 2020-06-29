<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\PHPUnitConstraint;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ValueObject;
use PHPUnit\Framework\Constraint\Constraint as AbstractPHPUnitConstraint;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use function sprintf;
use function trim;

class ContentItemEquals extends AbstractPHPUnitConstraint
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $expectedContent;

    public function __construct(Content $expectedContent)
    {
        $this->expectedContent = $expectedContent;
    }

    public function evaluate($content, string $description = '', bool $returnResult = false): bool
    {
        if (!$content instanceof Content) {
            return false;
        }
        if ($this->expectedContent === $content) {
            return true;
        }

        $comparatorFactory = ComparatorFactory::getInstance();

        try {
            // Note: intentionally didn't implement custom comparator, to re-use built-in ones
            // for chosen properties
            $this->compareValueObjects(
                $comparatorFactory,
                $this->expectedContent->getContentType(),
                $content->getContentType()
            );
            $this->compareValueObjects(
                $comparatorFactory,
                $this->expectedContent->getVersionInfo(),
                $content->getVersionInfo()
            );
            $this->compareValueObjects(
                $comparatorFactory,
                $this->expectedContent->getThumbnail(),
                $content->getThumbnail()
            );
            $this->compareArrays(
                $comparatorFactory,
                $this->expectedContent->fields,
                $content->fields
            );
        } catch (ComparisonFailure $f) {
            if ($returnResult) {
                return false;
            }

            $msg = sprintf(
                "%s\nContent item [%d] \"%s\" is not the same as [%d] \"%s\"\n%s",
                $description,
                $this->expectedContent->id,
                $this->expectedContent->getName(),
                $content->id,
                $content->getName(),
                $f->getMessage()
            );

            throw new ExpectationFailedException(trim($msg), $f);
        }

        return true;
    }

    public function toString(): string
    {
        return sprintf(
            'is the same as Content item [%d] "%s"',
            $this->expectedContent->id,
            $this->expectedContent->getName()
        );
    }

    protected function failureDescription($content): string
    {
        return sprintf(
            'Content item [%d] "%s" has the same data as [%d] "%s"',
            $content->id,
            $content->getName(),
            $this->expectedContent->id,
            $this->expectedContent->getName()
        );
    }

    private function compareValueObjects(
        ComparatorFactory $comparatorFactory,
        ?ValueObject $expected,
        ?ValueObject $actual
    ): void {
        $comparator = $comparatorFactory->getComparatorFor(
            $expected,
            $actual
        );

        $comparator->assertEquals(
            $expected,
            $actual,
        );
    }

    private function compareArrays(
        ComparatorFactory $comparatorFactory,
        array $expected,
        array $actual
    ): void {
        $comparator = $comparatorFactory->getComparatorFor(
            $expected,
            $actual
        );

        $comparator->assertEquals(
            $expected,
            $actual,
        );
    }
}
