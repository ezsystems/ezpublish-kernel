<?php
/**
 * File containing the LegacyBasedTestCase class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Tests;

use PHPUnit_Framework_TestCase;

/**
 * Base test case for legacy based tests.
 */
abstract class LegacyBasedTestCase extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        if ( !isset( $_ENV['legacyKernel'] ) )
            self::markTestSkipped( 'Legacy kernel is needed to run this test.' );
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    protected function getLegacyKernel()
    {
        return $_ENV['legacyKernel'];
    }
}
