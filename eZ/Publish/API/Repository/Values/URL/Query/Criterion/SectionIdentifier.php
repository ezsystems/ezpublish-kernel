<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\URL\Query\Criterion;

/**
 * Matches URLs which used by content placed in specified section identifiers.
 */
class SectionIdentifier extends Matcher
{
    /**
     * Identifiers of related content section.
     *
     * @var string[]
     */
    public $sectionIdentifiers;

    /**
     * @param string[] $sectionIdentifiers
     */
    public function __construct(array $sectionIdentifiers)
    {
        $this->sectionIdentifiers = $sectionIdentifiers;
    }
}
