<?php

/**
 * File containing the DefaultAuthenticationSuccessHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler as BaseSuccessHandler;

class DefaultAuthenticationSuccessHandler extends BaseSuccessHandler
{
    /**
     * Injects the ConfigResolver to potentially override default_target_path for redirections after authentication success.
     *
     * @param ConfigResolverInterface $configResolver
     */
    public function setConfigResolver(ConfigResolverInterface $configResolver)
    {
        $defaultPage = $configResolver->getParameter('default_page');
        if ($defaultPage !== null) {
            $this->options['default_target_path'] = $defaultPage;
        }
    }
}
