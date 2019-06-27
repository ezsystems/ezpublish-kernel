<?php
/**
 * File containing the Repository Context class for EzBehatBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformBehatBundle\Context;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Repository\Values\User\UserReference;

/**
 * Repository Context Trait.
 * Everything repository related should go here.
 * To be used by all the other behat contexts that need the repository.
 * Any Context tha uses this trait will automatically login the admin user,
 * so any operation that needs admin permissions can be executed.
 */
trait RepositoryContext
{
    /**
     * Default Administrator user id.
     */
    private $adminUserId = 14;

    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /**
     * @param $repository \eZ\Publish\API\Repository\Repository $repository
     */
    protected function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository $repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @BeforeScenario
     */
    public function loginAdmin($event)
    {
        $this->repository->setCurrentUser(new UserReference($this->adminUserId));
    }
}
