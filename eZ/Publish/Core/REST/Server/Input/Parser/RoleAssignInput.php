<?php

/**
 * File containing the RoleAssignInput parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Values\RoleAssignment;

/**
 * Parser for RoleAssignInput.
 */
class RoleAssignInput extends BaseParser
{
    /**
     * Parser tools.
     *
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct.
     *
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct(ParserTools $parserTools)
    {
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignment
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('Role', $data)) {
            throw new Exceptions\Parser("Missing 'Role' element for RoleAssignInput.");
        }

        if (!is_array($data['Role']) || !array_key_exists('_href', $data['Role'])) {
            throw new Exceptions\Parser("Invalid 'Role' element for RoleAssignInput.");
        }

        try {
            $roleId = $this->requestParser->parseHref($data['Role']['_href'], 'roleId');
        } catch (Exceptions\InvalidArgumentException $e) {
            throw new Exceptions\Parser('Invalid format for <Role> reference in <RoleAssignInput>.');
        }

        // @todo XSD says that limitation is mandatory, but roles can be assigned without limitations
        $limitation = null;
        if (array_key_exists('limitation', $data) && is_array($data['limitation'])) {
            if (!array_key_exists('_identifier', $data['limitation'])) {
                throw new Exceptions\Parser("Missing '_identifier' attribute for Limitation.");
            }

            $limitation = $parsingDispatcher->parse(
                $data['limitation'],
                'application/vnd.ez.api.internal.limitation.' . $data['limitation']['_identifier']
            );
        }

        return new RoleAssignment($roleId, $limitation);
    }
}
