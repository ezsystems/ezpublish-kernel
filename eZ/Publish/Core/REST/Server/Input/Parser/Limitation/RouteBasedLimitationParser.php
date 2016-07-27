<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Limitation;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values;

/**
 * Generic limitation value parser.
 *
 * Instances are built with:
 * - The name of a route parameter, that will be searched for limitation values
 *   Example: "sectionId" from "/content/section/{sectionId}"
 * - The FQN of the limitation value object that the parser builds
 */
class RouteBasedLimitationParser extends BaseParser
{
    /**
     * Name of the route parameter.
     * Example: "sectionId".
     * @var string
     */
    private $limitationRouteParameterName;

    /**
     * Value object class built by the Parser.
     * Example: "eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation".
     * @var string
     */
    private $limitationClass;

    /**
     * LimitationParser constructor.
     *
     * @param string $limitationRouteParameterName
     * @param string $limitationClass
     */
    public function __construct($limitationRouteParameterName, $limitationClass)
    {
        $this->limitationRouteParameterName = $limitationRouteParameterName;
        $this->limitationClass = $limitationClass;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('_identifier', $data)) {
            throw new Exceptions\Parser("Missing '_identifier' attribute for Limitation.");
        }

        $limitationObject = $this->buildLimitation();

        if (!isset($data['values']['ref']) || !is_array($data['values']['ref'])) {
            throw new Exceptions\Parser('Invalid format for data values in Limitation.');
        }

        foreach ($data['values']['ref'] as $limitationValue) {
            if (!array_key_exists('_href', $limitationValue)) {
                throw new Exceptions\Parser('Invalid format for data values in Limitation.');
            }

            $limitationObject->limitationValues[] = $this->parseIdFromHref($limitationValue);
        }

        return $limitationObject;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    protected function buildLimitation()
    {
        return new $this->limitationClass();
    }

    /**
     * @param $limitationValue
     *
     * @return false|mixed
     */
    protected function parseIdFromHref($limitationValue)
    {
        return $this->requestParser->parseHref(
            $limitationValue['_href'],
            $this->limitationRouteParameterName
        );
    }
}
