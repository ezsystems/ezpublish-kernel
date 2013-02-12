<?php
/**
 * File contains: Test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests\Configuration\Parser\Ini;

use eZ\Publish\Core\Base\Configuration\Parser\Ini as IniParser;
use eZ\Publish\Core\Base\Tests\Configuration\Parser\Ini\Base;

/**
 * Test case for Parser\Ini class
 */
class PHPTest extends Base
{
    public function getParser()
    {
        return new IniParser(
            array( 'IniParserStrict' => true )
        );
    }
}
