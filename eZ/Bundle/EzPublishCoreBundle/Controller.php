<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{
    public function getRepository(): Repository
    {
        return $this->container->get('ezpublish.api.repository');
    }

    protected function getConfigResolver(): ConfigResolverInterface
    {
        return $this->container->get('ezpublish.config.resolver');
    }

    public function getGlobalHelper(): GlobalHelper
    {
        return $this->container->get('ezpublish.templating.global_helper');
    }

    /**
     * Returns the root location object for current siteaccess configuration.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getRootLocation(): Location
    {
        return $this->getGlobalHelper()->getRootLocation();
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'ezpublish.api.repository' => Repository::class,
                'ezpublish.config.resolver' => ConfigResolverInterface::class,
                'ezpublish.templating.global_helper' => GlobalHelper::class,
            ]
        );
    }
}
