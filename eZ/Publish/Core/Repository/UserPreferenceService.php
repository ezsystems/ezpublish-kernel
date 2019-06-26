<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository;

use Exception;
use eZ\Publish\API\Repository\UserPreferenceService as UserPreferenceServiceInterface;
use eZ\Publish\API\Repository\Values\UserPreference\UserPreference as APIUserPreference;
use eZ\Publish\API\Repository\Values\UserPreference\UserPreferenceList;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\SPI\Persistence\UserPreference\UserPreferenceSetStruct;
use eZ\Publish\SPI\Persistence\UserPreference\Handler as UserPreferenceHandler;
use eZ\Publish\SPI\Persistence\UserPreference\UserPreference;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;

class UserPreferenceService implements UserPreferenceServiceInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \eZ\Publish\SPI\Persistence\UserPreference\Handler */
    private $userPreferenceHandler;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\UserPreference\Handler $userPreferenceHandler
     */
    public function __construct(RepositoryInterface $repository, UserPreferenceHandler $userPreferenceHandler)
    {
        $this->repository = $repository;
        $this->userPreferenceHandler = $userPreferenceHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserPreferences(int $offset = 0, int $limit = 25): UserPreferenceList
    {
        $currentUserId = $this->getCurrentUserId();

        $list = new UserPreferenceList();

        $list->totalCount = $this->userPreferenceHandler->countUserPreferences($currentUserId);
        if ($list->totalCount > 0) {
            $list->items = array_map(function (UserPreference $spiUserPreference) {
                return $this->buildDomainObject($spiUserPreference);
            }, $this->userPreferenceHandler->loadUserPreferences($currentUserId, $offset, $limit));
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserPreference(array $userPreferenceSetStructs): void
    {
        $spiSetStructs = [];
        foreach ($userPreferenceSetStructs as $key => $userPreferenceSetStruct) {
            $spiSetStruct = new UserPreferenceSetStruct();
            $spiSetStruct->userId = $this->getCurrentUserId();

            if (empty($userPreferenceSetStruct->name)) {
                throw new InvalidArgumentException('name', $userPreferenceSetStruct->name . ' at index ' . $key);
            }

            $spiSetStruct->name = $userPreferenceSetStruct->name;

            try {
                $value = (string)$userPreferenceSetStruct->value;
            } catch (\Exception $exception) {
                throw new InvalidArgumentException('value', 'Can not convert value to string at index ' . $key);
            }

            $spiSetStruct->value = $value;
            $spiSetStructs[] = $spiSetStruct;
        }

        $this->repository->beginTransaction();
        try {
            foreach ($spiSetStructs as $spiSetStruct) {
                $this->userPreferenceHandler->setUserPreference($spiSetStruct);
            }
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUserPreference(string $userPreferenceName): APIUserPreference
    {
        $currentUserId = $this->getCurrentUserId();

        $userPreference = $this->userPreferenceHandler->getUserPreferenceByUserIdAndName(
            $currentUserId,
            $userPreferenceName
        );

        return $this->buildDomainObject($userPreference);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserPreferenceCount(): int
    {
        return $this->userPreferenceHandler->countUserPreferences(
            $this->getCurrentUserId()
        );
    }

    /**
     * Builds UserPreference domain object from ValueObject returned by Persistence API.
     *
     * @param \eZ\Publish\SPI\Persistence\UserPreference\UserPreference $spiUserPreference
     *
     * @return \eZ\Publish\API\Repository\Values\UserPreference\UserPreference
     */
    protected function buildDomainObject(UserPreference $spiUserPreference): APIUserPreference
    {
        return new APIUserPreference([
            'name' => $spiUserPreference->name,
            'value' => $spiUserPreference->value,
        ]);
    }

    private function getCurrentUserId(): int
    {
        return $this->repository
            ->getPermissionResolver()
            ->getCurrentUserReference()
            ->getUserId();
    }
}
