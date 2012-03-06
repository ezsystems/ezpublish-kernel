<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Legacy\IOUploadTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Legacy;

use PHPUnit_Extensions_PhptTestCase;

/**
 * Test case for IO file upload using Legacy storage class
 */
class IOUploadTest extends PHPUnit_Extensions_PhptTestCase
{
    public function __construct()
    {
        parent::__construct( __DIR__ . '/upload.phpt' );
    }

    public static function getRepository()
    {
        return include 'common.php';
    }
}
