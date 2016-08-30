<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\PathExpansion;

/**
 * Checks if a REST document path needs expansion.
 *
 * The path is the suite of items leading to one element in a REST generator:
 * ```
 * <foo>
 *   <bar>
 *     <foobar /><!--foo.bar.foobar-->
 *   </bar>
 * </foo>
 * ```
 */
interface PathExpansionChecker
{
    /**
     * Tests if the link at $documentPath must be expanded.
     *
     * @param string $documentPath Path in a rest generator (example: Content.Owner).
     *
     * @return bool
     */
    public function needsExpansion($documentPath);
}
