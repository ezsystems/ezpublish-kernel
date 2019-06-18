<?php

/**
 * File containing the Pattern RequestParser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\RequestParser;

use eZ\Publish\Core\REST\Common\RequestParser;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Pattern based Request parser.
 *
 * Handles 2 types of patterns to be used in an URL:
 *
 * - {foo} matches anything but a slash and is used to match the typical URL
 *   variable (e.g. an ID)
 * - {&foo} matches the slash, too, and is used to match only those URL
 *   variables, which may have a slash
 */
class Pattern implements RequestParser
{
    /**
     * Map of URL types to their URL patterns.
     *
     * @var array
     */
    protected $map = [];

    /**
     * Cache for compiled expressions.
     *
     * @var array
     */
    protected $compileCache = [];

    /**
     * Pattern regular sub-expression.
     */
    const STANDARD_VARIABLE_REGEX = '\{([A-Za-z-_]+)\}';

    /**
     * Pattern regular sub-expression that might contain slashes.
     */
    const SLASHES_VARIABLE_REGEX = '\{(?:\&\s*)([A-Za-z-_]+)\}';

    /**
     * Construct from optional initial map.
     *
     * @param array $map
     */
    public function __construct(array $map = [])
    {
        foreach ($map as $type => $pattern) {
            $this->addPattern($type, $pattern);
        }
    }

    /**
     * Adds a pattern for a type.
     *
     * @param string $type
     * @param string $pattern
     */
    public function addPattern($type, $pattern)
    {
        $this->map[$type] = $pattern;
        unset($this->compileCache[$type]);
    }

    /**
     * Parse URL and return the IDs contained in the URL.
     *
     * @param string $type
     * @param string $url
     *
     * @return array
     */
    public function parse($url)
    {
        foreach ($this->map as $pattern) {
            $pattern = $this->compile($pattern);
            if (preg_match($pattern, $url, $match)) {
                // remove numeric keys
                foreach ($match as $key => $value) {
                    if (is_numeric($key)) {
                        unset($match[$key]);
                    }
                }

                return $match;
            }
        }

        throw new Exceptions\InvalidArgumentException("URL '$url' did not match any route.");
    }

    /**
     * Compiles a given pattern to a PCRE regular expression.
     *
     * @param string $pattern
     *
     * @return string
     */
    protected function compile($pattern)
    {
        if (isset($this->compileCache[$pattern])) {
            return $this->compileCache[$pattern];
        }

        $pcre = '(^';

        do {
            switch (true) {
                case preg_match('(^[^{]+)', $pattern, $match):
                    $pattern = substr($pattern, strlen($match[0]));
                    $pcre .= preg_quote($match[0]);
                    break;

                case preg_match('(^' . self::STANDARD_VARIABLE_REGEX . ')', $pattern, $match):
                    $pattern = substr($pattern, strlen($match[0]));
                    $pcre .= '(?P<' . $match[1] . '>[^/]+)';
                    break;

                case preg_match('(^' . self::SLASHES_VARIABLE_REGEX . ')', $pattern, $match):
                    $pattern = substr($pattern, strlen($match[0]));
                    $pcre .= '(?P<' . $match[1] . '>.+)';
                    break;

                default:
                    throw new Exceptions\InvalidArgumentException("Invalid pattern part: '$pattern'.");
            }
        } while ($pattern);

        $pcre .= '$)S';

        $this->compileCache[$pattern] = $pcre;

        return $pcre;
    }

    /**
     * Generate a URL of the given type from the specified values.
     *
     * @param string $type
     * @param array $values
     *
     * @return string
     */
    public function generate($type, array $values = [])
    {
        if (!isset($this->map[$type])) {
            throw new Exceptions\InvalidArgumentException("No URL for type '$type' available.");
        }

        $url = $this->map[$type];
        preg_match_all(
            '(' . self::STANDARD_VARIABLE_REGEX . '|' . self::SLASHES_VARIABLE_REGEX . ')',
            $url,
            $matches,
            PREG_SET_ORDER
        );
        foreach ($matches as $matchSet) {
            $variableName = empty($matchSet[1]) ? $matchSet[2] : $matchSet[1];
            if (!isset($values[$variableName])) {
                throw new Exceptions\InvalidArgumentException("No value provided for '{$variableName}'.");
            }

            $url = str_replace($matchSet[0], $values[$variableName], $url);
            unset($values[$variableName]);
        }

        if (count($values)) {
            throw new Exceptions\InvalidArgumentException("Unused values in values array: '" . implode("', '", array_keys($values)) . "'.");
        }

        return $url;
    }

    public function parseHref($href, $attribute)
    {
        $parsingResult = $this->parse($href);

        if (!isset($parsingResult[$attribute])) {
            throw new Exceptions\InvalidArgumentException("No such attribute '$attribute' in route matched from $href\n" . print_r($parsingResult, true));
        }

        return $parsingResult[$attribute];
    }
}
