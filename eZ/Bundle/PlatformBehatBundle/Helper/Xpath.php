<?php
/**
 * File containing the Xpath helper for PlatformBehatBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace EzSystems\PlatformBehatBundle\Helper;

use Behat\Mink\Session;

/**
 * This class eases the xpath creation and handling also has methods to easy search for certain content.
 */
class Xpath
{
    /**
     * Initialize class.
     *
     * @param \Behat\Mink\Session $session Behat session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * This is a simple shortcut for
     * $this->session->getPage()->getSelectorsHandler()->xpathLiteral().
     *
     * @param string $text
     */
    public function literal($text)
    {
        return $this->session->getSelectorsHandler()->xpathLiteral($text);
    }

    /**
     * Find all elements that match XPath.
     *
     * @param string $xpath XPath to find the elements
     *
     * @return \Behat\Mink\Element\NodeElement[] Array with NodeEelments that match
     */
    public function findXpath($xpath)
    {
        return $this->session->getPage()->findAll('xpath', $xpath);
    }

    /**
     * Make XPath for a specific element/object using Behat selectors.
     *
     * @param string $element Type of element for the XPath
     * @param string $search String to search
     *
     * @return string XPath for the element/object
     */
    public function makeElementXpath($element, $search)
    {
        $selectorsHandler = $this->session->getSelectorsHandler();
        $literal = $selectorsHandler->xpathLiteral($search);

        // To be able to work on mink 1.6 (ezplatform) & mink 1.5 (5.4+ezpublish-community) w/o deprecation exceptions
        $selector = $selectorsHandler->isSelectorRegistered('named_partial') ?
            $selectorsHandler->getSelector('named_partial') :
            $selectorsHandler->getSelector('named');

        return $selector->translateToXPath(array($element, $literal));
    }

    /**
     * Find page objects/elements.
     *
     * @param string $element Object type
     * @param string $search Text to search for
     * @param null|string $prefix XPath prefix if needed
     *
     * @return \Behat\Mink\Element\NodeElement[] Array with NodeEelments that match
     */
    public function findObjects($element, $search, $prefix = null)
    {
        $xpath = $this->mergePrefixToXpath(
            $prefix,
            $this->makeElementXpath($element, $search)
        );

        return $this->findXpath($xpath);
    }

    /**
     * Default method to find link elements.
     *
     * @param string $search Text to search for
     * @param null|string $prefix XPath prefix if needed
     *
     * @return \Behat\Mink\Element\NodeElement[] Array with NodeEelments that matched
     */
    public function findLinks($search, $prefix = null)
    {
        return $this->findObjects('link', $search, $prefix);
    }

    /**
     * Default method to find button elements.
     *
     * @param string $search Text to search for
     * @param null|string $prefix XPath prefix if needed
     *
     * @return \Behat\Mink\Element\NodeElement[] Array with NodeEelments that matched
     */
    public function findButtons($search, $prefix = null)
    {
        return $this->findObjects('button', $search, $prefix);
    }

    /**
     * Default method to find fieldset elements.
     *
     * @param string $search Text to search for
     * @param null|string $prefix XPath prefix if needed
     *
     * @return \Behat\Mink\Element\NodeElement[] Array with NodeEelments that matched
     */
    public function findFieldsetss($search, $prefix = null)
    {
        return $this->findObjects('fieldset', $search, $prefix);
    }

    /**
     * Default method to find field elements.
     *
     * @param string $search Text to search for
     * @param null|string $prefix XPath prefix if needed
     *
     * @return \Behat\Mink\Element\NodeElement[] Array with NodeEelments that matched
     */
    public function findFields($search, $prefix = null)
    {
        return $this->findObjects('field', $search, $prefix);
    }

    /**
     * Default method to find content elements.
     *
     * @param string $search Text to search for
     * @param null|string $prefix XPath prefix if needed
     *
     * @return \Behat\Mink\Element\NodeElement[] Array with NodeEelments that matched
     */
    public function findContents($search, $prefix = null)
    {
        return $this->findObjects('content', $search, $prefix);
    }

    /**
     * Default method to find select elements.
     *
     * @param string $search Text to search for
     * @param null|string $prefix XPath prefix if needed
     *
     * @return \Behat\Mink\Element\NodeElement[] Array with NodeEelments that matched
     */
    public function findSelects($search, $prefix = null)
    {
        return $this->findObjects('select', $search, $prefix);
    }

    /**
     * Default method to find checkbox elements.
     *
     * @param string $search Text to search for
     * @param null|string $prefix XPath prefix if needed
     *
     * @return \Behat\Mink\Element\NodeElement[] Array with NodeEelments that matched
     */
    public function findCheckboxs($search, $prefix = null)
    {
        return $this->findObjects('checkbox', $search, $prefix);
    }

    /**
     * Default method to find radio elements.
     *
     * @param string $search Text to search for
     * @param null|string $prefix XPath prefix if needed
     *
     * @return \Behat\Mink\Element\NodeElement[] Array with NodeEelments that matched
     */
    public function findRadios($search, $prefix = null)
    {
        return $this->findObjects('radio', $search, $prefix);
    }

    /**
     * Default method to find file elements.
     *
     * @param string $search Text to search for
     * @param null|string $prefix XPath prefix if needed
     *
     * @return \Behat\Mink\Element\NodeElement[] Array with NodeEelments that matched
     */
    public function findFiles($search, $prefix = null)
    {
        return $this->findObjects('file', $search, $prefix);
    }

    /**
     * Default method to find option elements.
     *
     * @param string $search Text to search for
     * @param null|string $prefix XPath prefix if needed
     *
     * @return \Behat\Mink\Element\NodeElement[] Array with NodeEelments that matched
     */
    public function findOptions($search, $prefix = null)
    {
        return $this->findObjects('option', $search, $prefix);
    }

    /**
     * Default method to find table elements.
     *
     * @param string $search Text to search for
     * @param null|string $prefix XPath prefix if needed
     *
     * @return \Behat\Mink\Element\NodeElement[] Array with NodeEelments that matched
     */
    public function findTables($search, $prefix = null)
    {
        return $this->findObjects('table', $search, $prefix);
    }

    /**
     * Merge/inject prefix into multiple case XPath.
     *
     * ex:
     *   $xpath = '//h1 | //h2';
     *   $prefix = '//article';
     *   return "//article/.//h1 | //article/.//h2"
     *
     * @param string $prefix XPath prefix
     * @param string $xpath Complete XPath
     *
     * @return string XPath with prefixes (or original if no prefix passed)
     */
    public function mergePrefixToXpath($prefix, $xpath)
    {
        if (empty($prefix)) {
            return $xpath;
        }

        if ($prefix[strlen($prefix) - 1] !== '/') {
            $prefix .= '/';
        }

        return $prefix . implode("| $prefix", explode('|', $xpath));
    }
}
