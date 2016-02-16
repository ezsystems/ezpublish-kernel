<?php
/**
 * File containing the main context class for Browser.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace EzSystems\PlatformBehatBundle\Context\Browser;

use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\MinkAwareContext;
use EzSystems\BehatBundle\Helper\Xpath;
use PHPUnit_Framework_Assert as Assertion;

/**
 * Context has wide (generic) browser helper methods and classes.
 */
class Context implements MinkAwareContext
{
    use MinkTrait;
    use SubContext\CommonActions;
    use SubContext\Authentication;

    /**
     * @var \EzSystems\BehatBundle\Helper\Xpath
     */
    private $xpath;

    /**
     * @var array Array to map identifier to urls, should be set by child classes.
     *
     * Important:
     *  this is an associative array( ex: array( 'key' => '/some/url') ) the keys
     *  should be set on contexts(case insensitive)
     */
    public $pageIdentifierMap = array();

    /**
     * This will tell us which containers (design) to search, should be set by child classes.
     *
     * ex:
     * $mainAttributes = array(
     *      "content"   => "thisIsATag",
     *      "column"    => array( "class" => "thisIsTheClassOfTheColumns" ),
     *      "menu"      => "//xpath/for/the[menu]",
     *      ...
     * );
     *
     * Is possible to define the specific xpath for a block, and all the other
     * options won't be processed, however this should ONLY be used when testing
     * content, otherwise if something changes on block it won't work
     *
     * @var array This will have a ( identifier => array )
     */
    protected $mainAttributes = array();

    /**
     * @BeforeScenario
     */
    public function prepareHelpers()
    {
        // initialize Helpers
        $this->xpath = new Xpath($this->getSession());
    }

    /**
     * Initialize with generic information.
     */
    public function __construct()
    {
        // add home to the page identifiers
        $this->pageIdentifierMap += array(
            'home' => '/',
            'login' => '/login',
            'logout' => '/logout',
        );
    }

    /**
     * Getter for Xpath.
     *
     * @return \EzSystems\BehatBundle\Helper\Xpath
     */
    public function getXpath()
    {
        return $this->xpath;
    }

    /**
     * Returns the path associated with $pageIdentifier.
     *
     * @param string $pageIdentifier
     *
     * @return string URL path
     *
     * @throws \RuntimeException If $pageIdentifier is not set
     */
    public function getPathByPageIdentifier($pageIdentifier)
    {
        if (!isset($this->pageIdentifierMap[strtolower($pageIdentifier)])) {
            throw new \RuntimeException("Unknown page identifier '{$pageIdentifier}'.");
        }

        return $this->pageIdentifierMap[strtolower($pageIdentifier)];
    }

    /**
     * This should be seen as a complement to self::getTagsFor() where it will
     * get the respective tags from there and will make a valid Xpath string with
     * all OR's needed.
     *
     * @param array  $tags  Array of tags strings (ex: array( "a", "p", "h3", "table" ) )
     * @param string $xpath String to be concatenated to each tag
     *
     * @return string Final xpath
     */
    public function concatTagsWithXpath(array $tags, $xpath = null)
    {
        $finalXpath = '';
        for ($i = 0; !empty($tags[$i]); ++$i) {
            $finalXpath .= "//{$tags[$i]}$xpath";
            if (!empty($tags[$i + 1])) {
                $finalXpath .= ' | ';
            }
        }

        return $finalXpath;
    }

    /**
     * With this function we get a centralized way to define what are the possible
     * tags for a type of data.
     *
     * @param string $type Type of text (ie: if header/title, or list element, ...)
     *
     * @return array With element tag names
     *
     * @throws \Behat\Behat\Exception\InvalidArgumentException If the $type isn't defined
     */
    public function getTagsFor($type)
    {
        switch (strtolower($type)) {
            case 'topic':
            case 'header':
            case 'title':
                return array('h1', 'h2', 'h3');
            case 'list':
                return array('li');
            case 'input':
                return array('input', 'select', 'textarea');
        }

        throw new \InvalidArgumentException("Tag's for '$type' type not defined");
    }

    /**
     * Returns $url without its query string.
     *
     * @param string $url
     *
     * @return string Complete url without the query string
     */
    public function getUrlWithoutQueryString($url)
    {
        if (strpos($url, '?') !== false) {
            $url = substr($url, 0, strpos($url, '?'));
        }

        return $url;
    }

    /**
     * Checks if links exist and in the following order (if intended)
     * Notice: if there are 3 links and we omit the middle link it will also be
     *  correct. It only checks order, not if there should be anything in
     *  between them.
     *
     * @param array $links Array with link text/title
     * @param Behat\Mink\Element\NodeElement[] $available
     * @param bool $checkOrder Boolean to verify (or not) the link order
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    protected function checkLinksExistence(array $links, array $available, $checkOrder = false)
    {
        $i = $passed = 0;
        $last = '';
        $messageAfter = '';
        foreach ($links as $link) {
            if (!$checkOrder) {
                $i = 0;
            }

            // find the object
            while (!empty($available[$i])
                && strpos($available[$i]->getText(), $link) === false
            ) {
                ++$i;
            }

            if ($checkOrder && !empty($last)) {
                $messageAfter = "after '$last'";
            }

            // check if the link was found or the $i >= $count
            $test = true;
            if (empty($available[$i])) {
                $test = false;
            }
            Assertion::assertTrue($test, "Couldn't find '$link'" . $messageAfter);

            ++$passed;
            $last = $link;
        }

        Assertion::assertEquals(
            count($links),
            $passed,
            "Expected to evaluate '" . count($links) . "' links evaluated '{$passed}'"
        );
    }

    /**
     * Fetchs the first integer in the string.
     *
     * @param string $string Text
     *
     * @return int Integer found on text
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function getNumberFromString($string)
    {
        preg_match('/(?P<digit>\d+)/', $string, $result);
        Assertion::assertNotEmpty($result['digit'], "Expected to find a number in '$string' found none");

        return (int)$result['digit'];
    }

    /**
     * Verifies if a row as the expected columns, position of columns can be added
     * for a more accurated assertion.
     *
     * @param \Behat\Mink\Element\NodeElement $row Table row node element
     * @param string[] $columns Column text to assert
     * @param string[]|int[] $columnsPositions Columns positions in int or string (number must be in string)
     *
     * @return bool
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function existTableRow(NodeElement $row, array $columns, array $columnsPositions = null)
    {
        // find which kind of column is in this row
        $elType = $row->find('xpath', '/th');
        $type = (empty($elType)) ? '/td' : '/th';

        $max = count($columns);
        for ($i = 0; $i < $max; ++$i) {
            $position = '';
            if (!empty($columnsPositions[$i])) {
                $position = "[{$this->getNumberFromString($columnsPositions[$i])}]";
            }

            $el = $row->find('xpath', "$type$position");

            // check if match with expected if not return false
            if ($el === null || $columns[$i] !== $el->getText()) {
                return false;
            }
        }

        // if we're here then it means all have ran as expected
        return true;
    }

    /**
     * Find a(all) table row(s) that match the column text.
     *
     * @param string        $text       Text to be found
     * @param string|int    $column     In which column the text should be found
     * @param string        $tableXpath If there is a specific table
     *
     * @return \Behat\Mink\Element\NodeElement[]
     */
    public function getTableRow($text, $column = null, $tableXpath = null)
    {
        // check column
        if (!empty($column)) {
            if (is_integer($column)) {
                $columnNumber = "[$column]";
            } else {
                $columnNumber = "[{$this->getNumberFromString($column)}]";
            }
        } else {
            $columnNumber = '';
        }

        // get all possible elements
        $elements = array_merge(
            $this->getXpath()->findXpath("$tableXpath//tr/th$columnNumber"),
            $this->getXpath()->findXpath("$tableXpath//tr/td$columnNumber")
        );

        $foundXpath = array();
        $total = count($elements);
        $i = 0;
        while ($i < $total) {
            if (strpos($elements[$i]->getText(), $text) !== false) {
                $foundXpath[] = $elements[$i]->getParent();
            }

            ++$i;
        }

        return $foundXpath;
    }

    /**
     * Find and return the row (<tr>) where the passed element is
     * This is useful when you intend to know if another element is in the same
     * row.
     *
     * @param \Behat\Mink\Element\NodeElement $element The element in the intended row
     *
     * @return \Behat\Mink\Element\NodeElement The <tr> element node
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function findRow(NodeElement $element)
    {
        $initialTag = $element->getTagName();

        while (strtolower($element->getTagName()) !== 'tr'
            && strtolower($element->getTagName()) !== 'body'
        ) {
            $element = $element->getParent();
        }

        Assertion::assertEquals(
            strtolower($element->getTagName()),
            'tr',
            "Couldn't find a parent of '$initialTag' that is a table row"
        );

        return $element;
    }

    /**
     * In a list of elements returns a certain element (found through xpath) that
     * is after a specific element (that is also found through xpath).
     *
     * <code>
     * findElementAfterElement(
     *      $arrayWithAllCellsOfARow,
     *      $xpathForALabel
     *      $xpathForAnInput
     * );
     * </code>
     *
     * @param array  $elements
     * @param string $firstXpath
     * @param string $secondXpath
     *
     * @return \Behat\Mink\Element\NodeElement|null Element if found null otherwise
     */
    public function findElementAfterElement(array $elements, $firstXpath, $secondXpath)
    {
        $foundFirstXpath = false;
        foreach ($elements as $element) {
            // choose what xpath to use
            if (!$foundFirstXpath) {
                $xpath = $firstXpath;
            } else {
                $xpath = $secondXpath;
            }

            $foundElement = $element->find('xpath', $xpath);

            // element found, if first start to look for the second one
            // if second, than return this one
            if (!empty($foundElement)) {
                if (!$foundFirstXpath) {
                    $foundFirstXpath = true;
                } else {
                    return $foundElement;
                }
            }
        }

        return null;
    }

    /**
     * Verifies if the element has 'special' configuration on a attribute (default -> style).
     *
     * @param \Behat\Mink\Element\NodeElement $el The element that we want to test
     * @param string $characteristic Verify a specific characteristic from attribute
     * @param string $attribute Verify a specific attribute
     *
     * @return bool
     */
    public function isElementEmphasized(NodeElement $el, $characteristic = null, $attribute = 'style')
    {
        // verify it has the attribute we're looking for
        if (!$el->hasAttribute($attribute)) {
            return false;
        }

        // get the attribute
        $attr = $el->getAttribute($attribute);

        // check if want to test specific characteristic and if it is present
        if (!empty($characteristic) && strpos($attr, $characteristic) === false) {
            return false;
        }

        // if we're here it is emphasized
        return true;
    }

    /**
     * This method works is a complement to the $mainAttributes var.
     *
     * @param  string $block This should be an identifier for the block to use
     *
     * @return null|string XPath for the block
     *
     * @see $this->mainAttributes
     */
    public function makeXpathForBlock($block = null)
    {
        if (empty($block)) {
            return null;
        }

        $parameter = (isset($this->mainAttributes[strtolower($block)])) ?
            $this->mainAttributes[strtolower($block)] :
            null;

        Assertion::assertNotNull($parameter, "Element {$block} is not defined");

        $xpath = $this->mainAttributes[strtolower($block)];
        // check if value is a composed array
        if (is_array($xpath)) {
            // if there is an xpath defined look no more!
            if (isset($xpath['xpath'])) {
                return $xpath['xpath'];
            }

            $nuXpath = '';
            // verify if there is a tag
            if (isset($xpath['tag'])) {
                if (strpos($xpath['tag'], '/') === 0 || strpos($xpath['tag'], '(') === 0) {
                    $nuXpath = $xpath['tag'];
                } else {
                    $nuXpath = '//' . $xpath['tag'];
                }

                unset($xpath['tag']);
            } else {
                $nuXpath = '//*';
            }

            foreach ($xpath as $key => $value) {
                switch ($key) {
                    case 'text':
                        $att = 'text()';
                        break;
                    default:
                        $att = "@$key";
                }
                $nuXpath .= "[contains($att, {$this->getXpath()->literal($value)})]";
            }

            return $nuXpath;
        }

        //  if the string is an Xpath
        if (strpos($xpath, '/') === 0 || strpos($xpath, '(') === 0) {
            return $xpath;
        }

        // if xpath is an simple tag
        return "//$xpath";
    }
}
