<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests;

class ComparingServiceTest extends BaseTest
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    public function setUp(): void
    {
        parent::setUp();

        $repository = $this->getRepository();
        $this->contentService = $repository->getContentService();
    }
}
