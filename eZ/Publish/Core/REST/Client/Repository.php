<?php

/**
 * File containing the Repository class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\Repository as APIRepository;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\Core\REST\Common;

/**
 * REST Client Repository.
 *
 * @see \eZ\Publish\API\Repository\Repository
 */
class Repository implements APIRepository
{
    /**
     * @var \eZ\Publish\Core\REST\Client\SectionService
     */
    private $sectionService;

    /**
     * @var \eZ\Publish\Core\REST\Client\LanguageService
     */
    private $languageService;

    /**
     * @var \eZ\Publish\Core\REST\Client\UserService
     */
    private $userService;

    /**
     * @var \eZ\Publish\Core\REST\Client\RoleService
     */
    private $roleService;

    /**
     * @var \eZ\Publish\Core\REST\Client\URLAliasService
     */
    private $urlAliasService;

    /**
     * @var \eZ\Publish\Core\REST\Client\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\Core\REST\Client\ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var \eZ\Publish\Core\REST\Client\TrashService
     */
    private $trashService;

    /**
     * @var \eZ\Publish\Core\REST\Client\LocationService
     */
    private $locationService;

    /**
     * @var \eZ\Publish\Core\REST\Client\ObjectStateService
     */
    private $objectStateService;

    /**
     * @var \eZ\Publish\Core\REST\Client\IOService
     */
    private $ioService;

    /**
     * @var \eZ\Publish\Core\REST\Client\FieldTypeService
     */
    private $fieldTypeService;

    /**
     * Client.
     *
     * @var \eZ\Publish\Core\REST\Client\HttpClient
     */
    private $client;

    /**
     * Input parsing dispatcher.
     *
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    private $inputDispatcher;

    /**
     * @var \eZ\Publish\Core\REST\Common\Output\Visitor
     */
    private $outputVisitor;

    /**
     * @var \eZ\Publish\Core\REST\Common\RequestParser
     */
    private $requestParser;

    /**
     * @var \eZ\Publish\SPI\FieldType\FieldType[]
     */
    private $fieldTypes;

    /**
     * Instantiates the REST Client repository.
     *
     * @param \eZ\Publish\Core\REST\Client\HttpClient $client
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $outputVisitor
     * @param \eZ\Publish\Core\REST\Common\RequestParser $requestParser
     * @param \eZ\Publish\SPI\FieldType\FieldType[] $fieldTypes
     */
    public function __construct(HttpClient $client, Common\Input\Dispatcher $inputDispatcher, Common\Output\Visitor $outputVisitor, Common\RequestParser $requestParser, array $fieldTypes)
    {
        $this->client = $client;
        $this->inputDispatcher = $inputDispatcher;
        $this->outputVisitor = $outputVisitor;
        $this->requestParser = $requestParser;
        $this->fieldTypes = $fieldTypes;
    }

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::getCurrentUserReference() instead.
     *
     * Get current user.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getCurrentUser()
    {
        return null;
    }

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::getCurrentUserReference() instead.
     *
     * Get current user.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserReference
     */
    public function getCurrentUserReference()
    {
        return null;
    }

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::setCurrentUserReference() instead.
     *
     * Sets the current user to the given $user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $user
     *
     * @return void
     */
    public function setCurrentUser(UserReference $user)
    {
        throw new Exceptions\MethodNotAllowedException(
            'It is not allowed to set a current user in this implementation. Please use a corresponding authenticating HttpClient instead.'
        );
    }

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::hasAccess() instead.
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $user
     *
     * @return bool|\eZ\Publish\API\Repository\Values\User\Limitation[] if limitations are on this function an array of limitations is returned
     */
    public function hasAccess($module, $function, UserReference $user = null)
    {
        // @todo: Implement
    }

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::canUser() instead.
     *
     * Indicates if the current user is allowed to perform an action given by the function on the given
     * objects.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If any of the arguments are invalid
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If value of the LimitationValue is unsupported
     *
     * @param string $module The module, aka controller identifier to check permissions on
     * @param string $function The function, aka the controller action to check permissions on
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object The object to check if the user has access to
     * @param mixed $targets The location, parent or "assignment" value object, or an array of the same
     *
     * @return bool
     */
    public function canUser($module, $function, ValueObject $object, $targets = null)
    {
        // @todo: Implement
    }

    /**
     * Get Content Service.
     *
     * Get service object to perform operations on Content objects and it's aggregate members.
     *
     * @return \eZ\Publish\API\Repository\ContentService
     */
    public function getContentService()
    {
        if (null === $this->contentService) {
            $this->contentService = new ContentService(
                $this->client,
                $this->inputDispatcher,
                $this->outputVisitor,
                $this->requestParser,
                $this->getContentTypeService()
            );
        }

        return $this->contentService;
    }

    /**
     * Get Content Language Service.
     *
     * Get service object to perform operations on Content language objects
     *
     * @return \eZ\Publish\API\Repository\LanguageService
     */
    public function getContentLanguageService()
    {
        if (null === $this->languageService) {
            $this->languageService = new LanguageService(
                $this->getContentService(),
                'eng-US',
                $this->client,
                $this->inputDispatcher,
                $this->outputVisitor,
                $this->requestParser
            );
        }

        return $this->languageService;
    }

    /**
     * Get Content Type Service.
     *
     * Get service object to perform operations on Content Type objects and it's aggregate members.
     * ( Group, Field & FieldCategory )
     *
     * @return \eZ\Publish\API\Repository\ContentTypeService
     */
    public function getContentTypeService()
    {
        if (null === $this->contentTypeService) {
            $this->contentTypeService = new ContentTypeService(
                $this->client,
                $this->inputDispatcher,
                $this->outputVisitor,
                $this->requestParser
            );
        }

        return $this->contentTypeService;
    }

    /**
     * Get Content Location Service.
     *
     * Get service object to perform operations on Location objects and subtrees
     *
     * @return \eZ\Publish\API\Repository\LocationService
     */
    public function getLocationService()
    {
        if (null === $this->locationService) {
            $this->locationService = new LocationService(
                $this->client,
                $this->inputDispatcher,
                $this->outputVisitor,
                $this->requestParser
            );
        }

        return $this->locationService;
    }

    /**
     * Get Content Trash service.
     *
     * Trash service allows to perform operations related to location trash
     * (trash/untrash, load/list from trash...)
     *
     * @return \eZ\Publish\API\Repository\TrashService
     */
    public function getTrashService()
    {
        if (null === $this->trashService) {
            $this->trashService = new TrashService(
                $this->getLocationService(),
                $this->client,
                $this->inputDispatcher,
                $this->outputVisitor,
                $this->requestParser
            );
        }

        return $this->trashService;
    }

    /**
     * Get Content Section Service.
     *
     * Get Section service that lets you manipulate section objects
     *
     * @return \eZ\Publish\API\Repository\SectionService
     */
    public function getSectionService()
    {
        if (null === $this->sectionService) {
            $this->sectionService = new SectionService(
                $this->client,
                $this->inputDispatcher,
                $this->outputVisitor,
                $this->requestParser
            );
        }

        return $this->sectionService;
    }

    /**
     * Get Search Service.
     *
     * Get search service that lets you find content objects
     *
     * @return \eZ\Publish\API\Repository\SearchService
     */
    public function getSearchService()
    {
        throw new \RuntimeException('@todo: Implement.');
    }

    /**
     * Get User Service.
     *
     * Get service object to perform operations on Users and UserGroup
     *
     * @return \eZ\Publish\API\Repository\UserService
     */
    public function getUserService()
    {
        if (null === $this->userService) {
            $this->userService = new UserService(
                $this->client,
                $this->inputDispatcher,
                $this->outputVisitor,
                $this->requestParser
            );
        }

        return $this->userService;
    }

    /**
     * Get IO Service.
     *
     * Get service object to perform operations on binary files
     *
     * @return \eZ\Publish\API\Repository\IOService
     */
    public function getIOService()
    {
        if (null === $this->ioService) {
            $this->ioService = new IOService(
                $this->client,
                $this->inputDispatcher,
                $this->outputVisitor,
                $this->requestParser
            );
        }

        return $this->ioService;
    }

    /**
     * Get RoleService.
     *
     * @return \eZ\Publish\API\Repository\RoleService
     */
    public function getRoleService()
    {
        if (null === $this->roleService) {
            $this->roleService = new RoleService(
                $this->getUserService(),
                $this->client,
                $this->inputDispatcher,
                $this->outputVisitor,
                $this->requestParser
            );
        }

        return $this->roleService;
    }

    /**
     * Get URLAliasService.
     *
     * @return \eZ\Publish\API\Repository\URLAliasService
     */
    public function getURLAliasService()
    {
        if (null === $this->urlAliasService) {
            $this->urlAliasService = new URLAliasService(
                $this->client,
                $this->inputDispatcher,
                $this->outputVisitor,
                $this->requestParser
            );
        }

        return $this->urlAliasService;
    }

    /**
     * Get URLWildcardService.
     *
     * @return \eZ\Publish\API\Repository\URLWildcardService
     */
    public function getURLWildcardService()
    {
        throw new \RuntimeException('@todo: Implement');
    }

    /**
     * Get ObjectStateService.
     *
     * @return \eZ\Publish\API\Repository\ObjectStateService
     */
    public function getObjectStateService()
    {
        if (null === $this->objectStateService) {
            $this->objectStateService = new ObjectStateService(
                $this->client,
                $this->inputDispatcher,
                $this->outputVisitor,
                $this->requestParser
            );
        }

        return $this->objectStateService;
    }

    /**
     * Get FieldTypeService.
     *
     * @return \eZ\Publish\API\Repository\FieldTypeService
     */
    public function getFieldTypeService()
    {
        if (null === $this->fieldTypeService) {
            $this->fieldTypeService = new FieldTypeService($this->fieldTypes);
        }

        return $this->fieldTypeService;
    }

    /**
     * Get PermissionResolver.
     *
     * @return \eZ\Publish\API\Repository\PermissionResolver
     */
    public function getPermissionResolver()
    {
        throw new \RuntimeException('@todo: Implement');
    }

    /**
     * Begin transaction.
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        // @todo: Implement / discuss
    }

    /**
     * Commit transaction.
     *
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function commit()
    {
        // @todo: Implement / discuss
    }

    /**
     * Rollback transaction.
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function rollback()
    {
        // @todo: Implement / discuss
    }
}
