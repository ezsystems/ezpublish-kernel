<?php

/**
 * File containing the Session parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\API\Repository\UserService;

/**
 * Value for Session.
 */
class Session extends BaseParser
{
    /** @var \eZ\Publish\Core\REST\Common\Input\ParserTools */
    protected $parserTools;

    /**
     * User Service.
     *
     * @var \eZ\Publish\Core\REST\Client\userService
     */
    protected $userService;

    /**
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     * @param \eZ\Publish\API\Repository\UserService $userService
     */
    public function __construct(ParserTools $parserTools, UserService $userService)
    {
        $this->parserTools = $parserTools;
        $this->userService = $userService;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('_href', $data['User'])) {
            throw new Exceptions\Parser("Missing '_href' attribute for User element in Session.");
        }

        $userId = $this->requestParser->parseHref($data['User']['_href'], 'userId');

        $user = $this->userService->loadUser($userId);

        return new Values\UserSession(
            $user,
            $data['name'],
            $data['identifier'],
            $data['csrfToken'],
            null
        );
    }
}
