<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\IOUploadTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy;

use PHPUnit_Extensions_PhptTestCase;

/**
 * Test case for IO file upload using Legacy storage class
 */
class IOUploadPHPT extends PHPUnit_Extensions_PhptTestCase
{
    public function __construct()
    {
        parent::__construct( __DIR__ . '/upload.phpt' );
    }

    // this method needs to be public static so upload.phpt test
    // can get ahold of Repository object
    public static function getRepository()
    {
        return Utils::getRepository();
    }
}
