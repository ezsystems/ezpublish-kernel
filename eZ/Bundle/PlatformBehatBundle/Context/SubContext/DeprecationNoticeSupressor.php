<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformBehatBundle\Context\SubContext;

trait DeprecationNoticeSupressor
{
    /**
     * Stores the original php error reporting value.
     */
    private $originalErrorReporting;

    /**
     * @BeforeScenario
     */
    public function suppressDepreciationNotices()
    {
        $this->originalErrorReporting = error_reporting(E_ALL & ~E_USER_DEPRECATED);
    }

    /**
     * @AfterScenario
     */
    public function restoreErrorReporting()
    {
        error_reporting($this->originalErrorReporting);
    }
}
