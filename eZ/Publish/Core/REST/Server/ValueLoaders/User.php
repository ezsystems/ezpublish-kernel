<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\ValueLoaders;

use eZ\Publish\Core\REST\Server\Values\RestUser;

class User extends RepositoryBased implements ValueLoaderInterface
{
    /**
     * @return \eZ\Publish\Core\REST\Server\Values\RestUser
     */
    public function load($parameters)
    {
        $userService = $this->getRepository()->getUserService();
        $contentService = $this->getRepository()->getContentService();
        $contentTypeService = $this->getRepository()->getContentTypeService();
        $locationService = $this->getRepository()->getLocationService();

        $user = $userService->loadUser($parameters['userId']);
        $contentType = $contentTypeService->loadContentType($user->contentInfo->contentTypeId);
        $mainLocation = $locationService->loadLocation($user->contentInfo->mainLocationId);
        $relations = $contentService->loadRelations($user->versionInfo);

        return new RestUser($user, $contentType, $user->contentInfo, $mainLocation, $relations);
    }
}
