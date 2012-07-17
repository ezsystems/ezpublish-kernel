<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\InMemory\Utils class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\InMemory;

use eZ\Publish\Core\Repository\Repository,
    eZ\Publish\Core\Repository\ValidatorService,
    eZ\Publish\Core\Repository\FieldTypeTools,
    eZ\Publish\Core\IO\InMemoryHandler as InMemoryIOHandler,
    eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryPersistenceHandler;

/**
 * Utils class for InMemory tesst
 */
abstract class Utils
{
    public static function getRepository( array $serviceSettings )
    {
        return new Repository(
            new InMemoryPersistenceHandler( new ValidatorService, new FieldTypeTools ),
            new InMemoryIOHandler(),
            $serviceSettings
        );
    }
}
