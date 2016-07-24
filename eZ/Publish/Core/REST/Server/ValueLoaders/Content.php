<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\ValueLoaders;

use eZ\Publish\Core\REST\Server\Values\RestContent;

class Content extends RepositoryBased implements ValueLoaderInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    public function load($parameters)
    {
        $content = $this->getRepository()->getContentService()->loadContent($parameters['contentId']);

        return new RestContent($content->contentInfo);
    }
}
