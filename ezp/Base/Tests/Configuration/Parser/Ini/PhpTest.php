<?php
/**
 * File contains: ezp\Base\Tests\IniParserTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Tests\Configuration\Parser\Ini;
use ezp\Base\Configuration\Parser\Ini as IniParser,
    ezp\Base\Tests\Configuration\Parser\Ini\Base;

/**
 * Test case for Parser\Ini class
 */
class PHPTest extends Base
{
    public function getParser()
    {
        return new IniParser(
            null,
            array(
                'base' => array(
                    'Configuration' => array( 'IniParserStrict' => true )
                )
            )
        );
    }
}
