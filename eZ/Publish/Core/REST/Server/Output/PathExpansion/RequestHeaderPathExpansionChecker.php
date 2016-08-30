<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\PathExpansion;

use eZ\Publish\Core\MVC\Symfony\RequestStackAware;

/**
 * Checks if a resource link needs to be expanded based on the contents of the x-ez-embed-value
 * header from the master request.
 */
class RequestHeaderPathExpansionChecker implements PathExpansionChecker
{
    use RequestStackAware;

    /**
     * Tests if the link at $documentPath must be expanded.
     *
     * @param string $documentPath Path in a rest generator (example: Content.Owner).
     *
     * @return bool
     */
    public function needsExpansion($documentPath)
    {
        $request = $this->getRequestStack()->getMasterRequest();

        if (is_null($request)) {
            return false;
        }
        if (!$request->headers->has('x-ez-embed-value')) {
            return false;
        }

        $documentPath = strtolower($documentPath);
        foreach (explode(',', $request->headers->get('x-ez-embed-value')) as $requestedPath) {
            $requestedPath = strtolower($requestedPath);
            if ($requestedPath === $documentPath) {
                return true;
            }
            if (substr($requestedPath, 0, strlen($documentPath)) === $documentPath) {
                return true;
            }
        }

        return false;
    }
}
