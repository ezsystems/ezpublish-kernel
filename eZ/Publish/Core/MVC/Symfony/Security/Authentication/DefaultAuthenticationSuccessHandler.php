<?php

/**
 * File containing the DefaultAuthenticationSuccessHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzPlatformAdminUiBundle\EzPlatformAdminUiBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler as BaseSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

class DefaultAuthenticationSuccessHandler extends BaseSuccessHandler
{
    /**
     * @var array
     */
    private $siteAccessGroups;

    /**
     * @param HttpUtils $httpUtils
     * @param array $options
     * @param array $siteAccessGroups
     */
    public function __construct(HttpUtils $httpUtils, array $options = array(), array $siteAccessGroups = [])
    {
        parent::__construct($httpUtils, $options);
        $this->siteAccessGroups = $siteAccessGroups;
    }

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

    /**
     * Builds the target URL according to the defined options.
     * Overwrites default page after login for admin siteaccess.
     * @param Request $request
     *
     * @return string
     */
    protected function determineTargetUrl(Request $request)
    {
        if ($this->isAdmin($request)) {
            $this->options['default_target_path'] = 'ezplatform.dashboard';
        }

        return parent::determineTargetUrl($request);
    }

    /**
     * Decides if used siteaccess if in admin_group.
     * @param Request $request
     *
     * @return bool
     */
    private function isAdmin(Request $request): bool
    {
        $siteAccess = $request->attributes->get('siteaccess');

        return in_array($siteAccess->name, $this->siteAccessGroups[EzPlatformAdminUiBundle::ADMIN_GROUP_NAME], true);
    }
}
