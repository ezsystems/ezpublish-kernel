<?php

/**
 * File containing the SectionList class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * Section list view model.
 */
class SectionList extends RestValue
{
    /**
     * Sections.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Section[]
     */
    public $sections;

    /**
     * Path used to load the list of sections.
     *
     * @var string
     */
    public $path;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Section[] $sections
     * @param string $path
     */
    public function __construct(array $sections, $path)
    {
        $this->sections = $sections;
        $this->path = $path;
    }
}
