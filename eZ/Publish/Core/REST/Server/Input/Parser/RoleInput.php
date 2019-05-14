<?php

/**
 * File containing the RoleInput parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use EzSystems\EzPlatformRest\Input\BaseParser;
use EzSystems\EzPlatformRest\Input\ParsingDispatcher;
use EzSystems\EzPlatformRest\Input\ParserTools;
use eZ\Publish\API\Repository\RoleService;

/**
 * Parser for RoleInput.
 */
class RoleInput extends BaseParser
{
    /**
     * Role service.
     *
     * @var \eZ\Publish\API\Repository\RoleService
     */
    protected $roleService;

    /**
     * @var \EzSystems\EzPlatformRest\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\RoleService $roleService
     * @param \EzSystems\EzPlatformRest\Input\ParserTools $parserTools
     */
    public function __construct(RoleService $roleService, ParserTools $parserTools)
    {
        $this->roleService = $roleService;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRest\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        // Since RoleInput is used both for creating and updating role and identifier is not
        // required when updating role, we need to rely on PAPI to throw the exception on missing
        // identifier when creating a role
        // @todo Bring in line with XSD which says that identifier is required always

        $roleIdentifier = null;
        if (array_key_exists('identifier', $data)) {
            $roleIdentifier = $data['identifier'];
        }

        $roleCreateStruct = $this->roleService->newRoleCreateStruct($roleIdentifier);

        return $roleCreateStruct;
    }
}
