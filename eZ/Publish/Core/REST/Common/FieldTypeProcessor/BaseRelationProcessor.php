<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use Symfony\Component\Routing\RouterInterface;

abstract class BaseRelationProcessor
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function canMapContentHref()
    {
        return isset($this->router);
    }

    public function mapToContentHref($contentId)
    {
        return $this->router->generate(
            'ezpublish_rest_loadContent',
            ['contentId' => $contentId]
        );
    }
}
