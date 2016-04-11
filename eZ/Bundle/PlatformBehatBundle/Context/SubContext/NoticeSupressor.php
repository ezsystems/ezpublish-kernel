<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformBehatBundle\Context\SubContext;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;

trait NoticeSupressor
{
    /**
     * @BeforeScenario
     */
    public static function suppressDepreciationNotices(BeforeScenarioScope $scope)
    {
        error_reporting(E_ALL & ~E_USER_DEPRECATED);
    }
}
