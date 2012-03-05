<?php
/**
 * File containing the BaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub;

$repository = new RepositoryStub(
    __DIR__ . '/Fixtures',
    ( isset( $_ENV['backendVersion'] ) ? (int) $_ENV['backendVersion'] : 5 )
);

$repository->setCurrentUser(
    new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserStub(
        array(
            'id' => 14,
            'content'  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\ContentStub(
                array(
                    'contentId' => 14
                )
            )
        )
    )
);

return $repository;