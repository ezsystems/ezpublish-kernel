<?php

/**
 * File containing the BaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use Doctrine\DBAL\Connection;
use eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException;
use eZ\Publish\API\Repository\Exceptions\ForbiddenException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Tests\PHPUnitConstraint\ValidationErrorOccurs as PHPUnitConstraintValidationErrorOccurs;
use eZ\Publish\API\Repository\Tests\SetupFactory\Legacy;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Language;
use EzSystems\EzPlatformSolrSearchEngine\Tests\SetupFactory\LegacySetupFactory as LegacySolrSetupFactory;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\Core\REST\Client\Sessionable;
use DateTime;
use ArrayObject;
use Exception;
use PDOException;

/**
 * Base class for api specific tests.
 */
abstract class BaseTest extends TestCase
{
    /**
     * Maximum integer number accepted by the different backends.
     */
    const DB_INT_MAX = 2147483647;

    /** @var \eZ\Publish\API\Repository\Tests\SetupFactory */
    private $setupFactory;

    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();

        try {
            // Use setup factory instance here w/o clearing data in case test don't need to
            $repository = $this->getSetupFactory()->getRepository(false);

            // Set session if we are testing the REST backend to make it
            // possible to persist data in the memory backend during multiple
            // requests.
            if ($repository instanceof Sessionable) {
                $repository->setSession($id = md5(microtime()));
            }
        } catch (PDOException $e) {
            $this->fail(
                'The communication with the database cannot be established. ' .
                "This is required in order to perform the tests.\n\n" .
                'Exception: ' . $e
            );
        } catch (Exception $e) {
            $this->fail(
                'Cannot create a repository with predefined user. ' .
                'Check the UserService or RoleService implementation. ' .
                PHP_EOL . PHP_EOL .
                'Exception: ' . $e
            );
        }
    }

    /**
     * Resets the temporary used repository between each test run.
     */
    protected function tearDown()
    {
        $this->repository = null;
        parent::tearDown();
    }

    /**
     * Returns the ID generator, fitting to the repository implementation.
     *
     * @return \eZ\Publish\API\Repository\Tests\IdManager
     */
    protected function getIdManager()
    {
        return $this->getSetupFactory()->getIdManager();
    }

    /**
     * Generates a repository specific ID value.
     *
     * @param string $type
     * @param mixed $rawId
     *
     * @return mixed
     */
    protected function generateId($type, $rawId)
    {
        return $this->getIdManager()->generateId($type, $rawId);
    }

    /**
     * Parses a repository specific ID value.
     *
     * @param string $type
     * @param mixed $id
     *
     * @return mixed
     */
    protected function parseId($type, $id)
    {
        return $this->getIdManager()->parseId($type, $id);
    }

    /**
     * Returns a config setting provided by the setup factory.
     *
     * @param string $configKey
     *
     * @return mixed
     */
    protected function getConfigValue($configKey)
    {
        return $this->getSetupFactory()->getConfigValue($configKey);
    }

    /**
     * Tests if the currently tested api is based on a V4 implementation.
     *
     * @return bool
     */
    protected function isVersion4()
    {
        return isset($_ENV['backendVersion']) && '4' === $_ENV['backendVersion'];
    }

    /**
     * @param bool $initialInitializeFromScratch Only has an effect if set in first call within a test
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepository($initialInitializeFromScratch = true)
    {
        if (null === $this->repository) {
            $this->repository = $this->getSetupFactory()->getRepository($initialInitializeFromScratch);
        }

        return $this->repository;
    }

    /**
     * @return \eZ\Publish\API\Repository\Tests\SetupFactory
     */
    protected function getSetupFactory()
    {
        if (null === $this->setupFactory) {
            if (false === ($setupClass = getenv('setupFactory'))) {
                $setupClass = Legacy::class;
                putenv("setupFactory=${setupClass}");
            }

            if (false === ($fixtureDir = getenv('fixtureDir'))) {
                putenv('fixtureDir=Legacy');
            }

            if (false === class_exists($setupClass)) {
                throw new \ErrorException(
                    sprintf(
                        'Environment variable "setupFactory" does not reference an existing class: %s. Did you forget to install an package dependency?',
                        $setupClass
                    )
                );
            }

            $this->setupFactory = new $setupClass();
        }

        return $this->setupFactory;
    }

    /**
     * Asserts that properties given in $expectedValues are correctly set in
     * $actualObject.
     *
     * @param mixed[] $expectedValues
     * @param \eZ\Publish\API\Repository\Values\ValueObject $actualObject
     */
    protected function assertPropertiesCorrect(array $expectedValues, ValueObject $actualObject)
    {
        foreach ($expectedValues as $propertyName => $propertyValue) {
            if ($propertyValue instanceof ValueObject) {
                $this->assertStructPropertiesCorrect($propertyValue, $actualObject->$propertyName);
            } elseif (is_array($propertyValue)) {
                foreach ($propertyValue as $key => $value) {
                    if ($value instanceof ValueObject) {
                        $this->assertStructPropertiesCorrect($value, $actualObject->$propertyName[$key]);
                    } else {
                        $this->assertPropertiesEqual("$propertyName\[$key\]", $value, $actualObject->$propertyName[$key]);
                    }
                }
            } else {
                $this->assertPropertiesEqual($propertyName, $propertyValue, $actualObject->$propertyName);
            }
        }
    }

    /**
     * Asserts that properties given in $expectedValues are correctly set in
     * $actualObject.
     *
     * If the property type is array, it will be sorted before comparison.
     *
     * @TODO: introduced because of randomly failing tests, ref: https://jira.ez.no/browse/EZP-21734
     *
     * @param mixed[] $expectedValues
     * @param \eZ\Publish\API\Repository\Values\ValueObject $actualObject
     */
    protected function assertPropertiesCorrectUnsorted(array $expectedValues, ValueObject $actualObject)
    {
        foreach ($expectedValues as $propertyName => $propertyValue) {
            if ($propertyValue instanceof ValueObject) {
                $this->assertStructPropertiesCorrect($propertyValue, $actualObject->$propertyName);
            } else {
                $this->assertPropertiesEqual($propertyName, $propertyValue, $actualObject->$propertyName, true);
            }
        }
    }

    /**
     * Asserts all properties from $expectedValues are correctly set in
     * $actualObject. Additional (virtual) properties can be asserted using
     * $additionalProperties.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $expectedValues
     * @param \eZ\Publish\API\Repository\Values\ValueObject $actualObject
     * @param array $propertyNames
     */
    protected function assertStructPropertiesCorrect(ValueObject $expectedValues, ValueObject $actualObject, array $additionalProperties = [])
    {
        foreach ($expectedValues as $propertyName => $propertyValue) {
            if ($propertyValue instanceof ValueObject) {
                $this->assertStructPropertiesCorrect($propertyValue, $actualObject->$propertyName);
            } else {
                $this->assertPropertiesEqual($propertyName, $propertyValue, $actualObject->$propertyName);
            }
        }

        foreach ($additionalProperties as $propertyName) {
            $this->assertPropertiesEqual($propertyName, $expectedValues->$propertyName, $actualObject->$propertyName);
        }
    }

    /**
     * @see \eZ\Publish\API\Repository\Tests\BaseTest::assertPropertiesCorrectUnsorted()
     *
     * @param array $items An array of scalar values
     */
    private function sortItems(array &$items)
    {
        $sorter = function ($a, $b) {
            if (!is_scalar($a) || !is_scalar($b)) {
                $this->fail('Wrong usage: method ' . __METHOD__ . ' accepts only an array of scalar values');
            }

            return strcmp($a, $b);
        };
        usort($items, $sorter);
    }

    private function assertPropertiesEqual($propertyName, $expectedValue, $actualValue, $sortArray = false)
    {
        if ($expectedValue instanceof ArrayObject) {
            $expectedValue = $expectedValue->getArrayCopy();
        } elseif ($expectedValue instanceof DateTime) {
            $expectedValue = $expectedValue->format(DateTime::RFC850);
        }
        if ($actualValue instanceof ArrayObject) {
            $actualValue = $actualValue->getArrayCopy();
        } elseif ($actualValue instanceof DateTime) {
            $actualValue = $actualValue->format(DateTime::RFC850);
        }

        if ($sortArray && is_array($actualValue) && is_array($expectedValue)) {
            $this->sortItems($actualValue);
            $this->sortItems($expectedValue);
        }

        $this->assertEquals(
            $expectedValue,
            $actualValue,
            sprintf('Object property "%s" incorrect.', $propertyName)
        );
    }

    /**
     * Create a user in editor user group.
     *
     * @param string $login
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function createUserVersion1($login = 'user')
    {
        $repository = $this->getRepository();

        /* BEGIN: Inline */
        // ID of the "Editors" user group in an eZ Publish demo installation
        $editorsGroupId = 13;

        $userService = $repository->getUserService();

        // Instantiate a create struct with mandatory properties
        $userCreate = $userService->newUserCreateStruct(
            $login,
            "{$login}@example.com",
            'secret',
            'eng-US'
        );
        $userCreate->enabled = true;

        // Set some fields required by the user ContentType
        $userCreate->setField('first_name', 'Example');
        $userCreate->setField('last_name', 'User');

        // Load parent group for the user
        $group = $userService->loadUserGroup($editorsGroupId);

        // Create a new user instance.
        $user = $userService->createUser($userCreate, [$group]);
        /* END: Inline */

        return $user;
    }

    /**
     * Create a user in new user group with editor rights limited to Media Library (/1/48/).
     *
     * @uses ::createCustomUserVersion1()
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function createMediaUserVersion1()
    {
        return $this->createCustomUserVersion1(
            'Media Editor',
            'Editor',
            new SubtreeLimitation(['limitationValues' => ['/1/43/']])
        );
    }

    /**
     * Create a user with new user group and assign a existing role (optionally with RoleLimitation).
     *
     * @param string $userGroupName Name of the new user group to create
     * @param string $roleIdentifier Role identifier to assign to the new group
     * @param RoleLimitation|null $roleLimitation
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function createCustomUserVersion1($userGroupName, $roleIdentifier, RoleLimitation $roleLimitation = null)
    {
        return $this->createCustomUserWithLogin(
            'user',
            'user@example.com',
            $userGroupName,
            $roleIdentifier,
            $roleLimitation
        );
    }

    /**
     * Create a user with new user group and assign a existing role (optionally with RoleLimitation).
     *
     * @param string $login User login
     * @param string $email User e-mail
     * @param string $userGroupName Name of the new user group to create
     * @param string $roleIdentifier Role identifier to assign to the new group
     * @param RoleLimitation|null $roleLimitation
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function createCustomUserWithLogin(
        $login,
        $email,
        $userGroupName,
        $roleIdentifier,
        RoleLimitation $roleLimitation = null
    ) {
        $repository = $this->getRepository();

        /* BEGIN: Inline */
        // ID of the "Users" user group in an eZ Publish demo installation
        $rootUsersGroupId = $this->generateId('location', 4);

        $roleService = $repository->getRoleService();
        $userService = $repository->getUserService();

        // Get a group create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct('eng-US');
        $userGroupCreate->setField('name', $userGroupName);

        // Create new group with media editor rights
        $userGroup = $userService->createUserGroup(
            $userGroupCreate,
            $userService->loadUserGroup($rootUsersGroupId)
        );
        $roleService->assignRoleToUserGroup(
            $roleService->loadRoleByIdentifier($roleIdentifier),
            $userGroup,
            $roleLimitation
        );

        // Instantiate a create struct with mandatory properties
        $userCreate = $userService->newUserCreateStruct(
            $login,
            $email,
            'secret',
            'eng-US'
        );
        $userCreate->enabled = true;

        // Set some fields required by the user ContentType
        $userCreate->setField('first_name', 'Example');
        $userCreate->setField('last_name', ucfirst($login));

        // Create a new user instance.
        $user = $userService->createUser($userCreate, [$userGroup]);
        /* END: Inline */

        return $user;
    }

    /**
     * Create a user using given data.
     *
     * @param string $login
     * @param string $firstName
     * @param string $lastName
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup|null $userGroup optional user group, Editor by default
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function createUser($login, $firstName, $lastName, UserGroup $userGroup = null)
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();
        if (null === $userGroup) {
            $userGroup = $userService->loadUserGroup(13);
        }

        // Instantiate a create struct with mandatory properties
        $userCreate = $userService->newUserCreateStruct(
            $login,
            "{$login}@example.com",
            'secret',
            'eng-US'
        );
        $userCreate->enabled = true;

        // Set some fields required by the user ContentType
        $userCreate->setField('first_name', $firstName);
        $userCreate->setField('last_name', $lastName);

        // Create a new user instance.
        $user = $userService->createUser($userCreate, [$userGroup]);

        return $user;
    }

    /**
     * Only for internal use.
     *
     * Creates a \DateTime object for $timestamp in the current time zone
     *
     * @param int $timestamp
     *
     * @return \DateTime
     */
    public function createDateTime($timestamp = null)
    {
        $dateTime = new \DateTime();
        if ($timestamp !== null) {
            $dateTime->setTimestamp($timestamp);
        }

        return $dateTime;
    }

    /**
     * Calls given Repository's aggregated SearchHandler::refresh().
     *
     * Currently implemented only in Solr search engine.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    protected function refreshSearch(Repository $repository)
    {
        $setupFactory = $this->getSetupFactory();

        if (!$setupFactory instanceof LegacySolrSetupFactory) {
            return;
        }

        while (true) {
            $repositoryReflection = new \ReflectionObject($repository);
            // If the repository is decorated, we need to recurse in the "repository" property
            if (!$repositoryReflection->hasProperty('repository')) {
                break;
            }

            $repositoryProperty = $repositoryReflection->getProperty('repository');
            $repositoryProperty->setAccessible(true);
            $repository = $repositoryProperty->getValue($repository);
        }

        $searchHandlerProperty = new \ReflectionProperty($repository, 'searchHandler');
        $searchHandlerProperty->setAccessible(true);

        /** @var \EzSystems\EzPlatformSolrSearchEngine\Handler $searchHandler */
        $searchHandler = $searchHandlerProperty->getValue($repository);

        $searchHandler->commit();
    }

    /**
     * Create role of a given name with the given policies described by an array.
     *
     * @param $roleName
     * @param array $policiesData [['module' => 'content', 'function' => 'read', 'limitations' => []]
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function createRoleWithPolicies($roleName, array $policiesData)
    {
        $repository = $this->getRepository(false);
        $roleService = $repository->getRoleService();

        $roleCreateStruct = $roleService->newRoleCreateStruct($roleName);
        foreach ($policiesData as $policyData) {
            $policyCreateStruct = $roleService->newPolicyCreateStruct(
                $policyData['module'],
                $policyData['function']
            );

            if (isset($policyData['limitations'])) {
                foreach ($policyData['limitations'] as $limitation) {
                    $policyCreateStruct->addLimitation($limitation);
                }
            }

            $roleCreateStruct->addPolicy($policyCreateStruct);
        }

        $roleDraft = $roleService->createRole($roleCreateStruct);

        $roleService->publishRoleDraft($roleDraft);

        return $roleService->loadRole($roleDraft->id);
    }

    /**
     * Create user and assign new role with the given policies.
     *
     * @param string $login
     * @param array $policiesData list of policies in the form of <code>[ [ 'module' => 'name', 'function' => 'name'] ]</code>
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation|null $roleLimitation
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function createUserWithPolicies($login, array $policiesData, RoleLimitation $roleLimitation = null)
    {
        $repository = $this->getRepository(false);
        $roleService = $repository->getRoleService();
        $userService = $repository->getUserService();

        $repository->beginTransaction();
        try {
            $userCreateStruct = $userService->newUserCreateStruct(
                $login,
                "{$login}@test.local",
                $login,
                'eng-GB'
            );
            $userCreateStruct->setField('first_name', $login);
            $userCreateStruct->setField('last_name', $login);
            $user = $userService->createUser($userCreateStruct, [$userService->loadUserGroup(4)]);

            $role = $this->createRoleWithPolicies(uniqid('role_for_' . $login . '_', true), $policiesData);
            $roleService->assignRoleToUser($role, $user, $roleLimitation);

            $repository->commit();

            return $user;
        } catch (ForbiddenException | NotFoundException | UnauthorizedException $ex) {
            $repository->rollback();
            throw $ex;
        }
    }

    /**
     * @return \Doctrine\DBAL\Connection
     *
     * @throws \ErrorException
     */
    protected function getRawDatabaseConnection()
    {
        $connection = $this
            ->getSetupFactory()
            ->getServiceContainer()->get('ezpublish.api.storage_engine.legacy.connection');

        if (!$connection instanceof Connection) {
            throw new \RuntimeException(
                sprintf('Expected %s got %s', Connection::class, get_class($connection))
            );
        }

        return $connection;
    }

    /**
     * Executes the given callback passing raw Database Connection (\Doctrine\DBAL\Connection).
     * Returns the result returned by the given callback.
     *
     * **Note**: The method clears the entire persistence cache pool.
     *
     * @throws \Exception
     *
     * @param callable $callback
     *
     * @return mixed the return result of the given callback
     */
    public function performRawDatabaseOperation(callable $callback)
    {
        $repository = $this->getRepository(false);
        $repository->beginTransaction();
        try {
            $callback(
                $this->getRawDatabaseConnection()
            );
            $repository->commit();
        } catch (Exception $e) {
            $repository->rollback();
            throw $e;
        }

        /** @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface $cachePool */
        $cachePool = $this
            ->getSetupFactory()
            ->getServiceContainer()->get('ezpublish.cache_pool');

        $cachePool->clear();
    }

    /**
     * Traverse all errors for all fields in all Translations to find expected one.
     *
     * @param \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException $exception
     * @param string $expectedValidationErrorMessage
     */
    protected function assertValidationErrorOccurs(
        ContentFieldValidationException $exception,
        $expectedValidationErrorMessage
    ) {
        $constraint = new PHPUnitConstraintValidationErrorOccurs($expectedValidationErrorMessage);

        self::assertThat($exception, $constraint);
    }

    /**
     * Create 'folder' Content.
     *
     * @param array $names Folder names in the form of <code>['&lt;language_code&gt;' => '&lt;name&gt;']</code>
     * @param int $parentLocationId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content published Content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function createFolder(array $names, $parentLocationId)
    {
        $repository = $this->getRepository(false);
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        if (empty($names)) {
            throw new \RuntimeException(sprintf('%s expects non-empty names list', __METHOD__));
        }
        $mainLanguageCode = array_keys($names)[0];

        $struct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('folder'),
            $mainLanguageCode
        );
        foreach ($names as $languageCode => $translatedName) {
            $struct->setField('name', $translatedName, $languageCode);
        }
        $contentDraft = $contentService->createContent(
            $struct,
            [$locationService->newLocationCreateStruct($parentLocationId)]
        );

        return $contentService->publishVersion($contentDraft->versionInfo);
    }

    /**
     * Update 'folder' Content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param array $names Folder names in the form of <code>['&lt;language_code&gt;' => '&lt;name&gt;']</code>
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function updateFolder(Content $content, array $names)
    {
        $repository = $this->getRepository(false);
        $contentService = $repository->getContentService();

        $draft = $contentService->createContentDraft($content->contentInfo);
        $struct = $contentService->newContentUpdateStruct();
        $struct->initialLanguageCode = array_keys($names)[0];

        foreach ($names as $languageCode => $translatedName) {
            $struct->setField('name', $translatedName, $languageCode);
        }

        return $contentService->updateContent($draft->versionInfo, $struct);
    }

    /**
     * Add new Language to the Repository.
     *
     * @param string $languageCode
     * @param string $name
     * @param bool $enabled
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    protected function createLanguage(string $languageCode, string $name, bool $enabled = true): Language
    {
        $repository = $this->getRepository(false);

        $languageService = $repository->getContentLanguageService();
        $languageStruct = $languageService->newLanguageCreateStruct();
        $languageStruct->name = $name;
        $languageStruct->languageCode = $languageCode;
        $languageStruct->enabled = $enabled;

        return $languageService->createLanguage($languageStruct);
    }
}
