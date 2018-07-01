<?php

/**
 * File containing the RoleList parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Parser for UserList.
 */
class UserList extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role[]
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('User', $data) || !is_array($data['User'])) {
            throw new Exceptions\Parser("Missing 'User' element in UserRefList.");
        }

        $userList = array();
        foreach ($data['User'] as $userData) {
            $userList[] = new User(
                array(
                    'login' => $userData['login'],
                    'email' => $userData['email'],
                    'enabled' => (bool)$userData['enabled']
                )
            );
        }
        return $userList;
    }
}
