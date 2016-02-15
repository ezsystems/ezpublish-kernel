<?php
/**
 * File containing the Common Actions for Browser contexts
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace EzSystems\PlatformBehatBundle\Context\Browser\SubContext;

use EzSystems\BehatBundle\Helper\EzAssertion;
use EzSystems\BehatBundle\Helper\Gherkin as GherkinHelper;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use PHPUnit_Framework_Assert as Assertion;

/**
 * Class with the simple actions you can do in a browser
 *
 * @method \Behat\Mink\Session getSession
 */
trait CommonActions
{
    /**
     * @Given I am on :path
     * @When  I go to :path
     *
     * Visits the path ':path', relative to the configured 'base_url' parameter
     */
    public function visit( $path )
    {
        $this->getSession()->visit( $this->locatePath( $path ) );
    }

    /**
     * @Given I see :id (link/button/form)
     * @When  I can see :id (link/button/form)
     *
     * A "spin function" sentence to wait for a given element to appear.
     * Waits maximum 3 seconds, tries every 200ms
     *
     * @link http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html
     */
    public function iCanSee( $id )
    {
        $element = null;
        $session = $this->getSession();
        for ($i = 0; $i < 15; $i++) {
            if ($element = $session->getPage()->findById($id)) {
                break;
            }

            usleep(200 * 1000);// 1 million microseconds is 1 second
        }

        Assertion::assertTrue($element !== null, "Item '{$id}' did not appear on the page within 3 seconds");
    }

    /**
     * @Given I am on/at the homepage
     * @Given I am on/at (the) :page page
     * @When I go to the homepage
     * @When  I go to (the) :page page
     *
     * Visits the page identified by ':page', or the homepage.
     * Asserts that http response status code is not >= 400
     */
    public function iAmOnPage( $page = 'home' )
    {
        $this->visit( $this->getPathByPageIdentifier( $page ) );
        $this->checkForExceptions();
    }

    /**
     * Checks the output of the latest session page for Symfony exceptions.
     *
     * If one is found, a failed assertion is executed, with the exception details + formatted stacktrace.
     */
    protected function checkForExceptions()
    {
        $exceptionElements = $this->getXpath()->findXpath("//div[@class='text-exception']/h1");
        $exceptionStackTraceItems = $this->getXpath()->findXpath("//ol[@id='traces-0']/li");
        if (count($exceptionElements) > 0) {
            $exceptionElement = $exceptionElements[0];
            $exceptionLines = [$exceptionElement->getText(), ''];

            foreach ($exceptionStackTraceItems as $stackTraceItem) {
                $html = $stackTraceItem->getHtml();
                $html = substr($html, 0, strpos($html, '<a href', 1));
                $html = htmlspecialchars_decode(strip_tags($html));
                $html = preg_replace('/\s+/', ' ', $html);
                $html = str_replace('  (', '(', $html);
                $html = str_replace(' ->', '->', $html);
                $exceptionLines[] = trim($html);
            }
            $message = 'An exception occured during rendering:' . implode("\n", $exceptionLines);
            Assertion::assertTrue(false, $message);
        }
    }

    /**
     * @Then I should see a :label input field
     */
    public function seeInputField($label)
    {
        $field = $this->getSession()->getPage()->findField($label);
        if (!$field) {
            throw new \Exception("Field '$label' not found");
        }
    }
    /**
     * @When I fill in :field with :value
     * @When I set :field as empty
     *
     * Fill field identified by ':field' with ':value'
     */
    public function fillFieldWithValue( $field, $value = '' )
    {
        $this->getSession()->getPage()->fillField( $field, $value );
    }

    /**
     * @Then I should be at/on (the) homepage
     * @Then I should be at/on (the) :page page
     *
     * Asserts that the current page is the one identified by ':page', or the homepage.
     */
    public function iShouldBeOnPage( $pageIdentifier = 'home' )
    {
        $currentUrl = $this->getUrlWithoutQueryString( $this->getSession()->getCurrentUrl() );

        $expectedUrl = $this->locatePath( $this->getPathByPageIdentifier( $pageIdentifier ) );

        Assertion::assertEquals(
            $expectedUrl,
            $currentUrl,
            "Unexpected URL of the current site. Expected: '$expectedUrl'. Actual: '$currentUrl'."
        );
    }

    /**
     * @Given I clicked on/at (the) :button button
     * @When I click on/at (the) :button button
     *
     * Clicks the button identified by ':button'
     */
    public function iClickAtButton( $button )
    {
        $this->onPageSectionIClickAtButton( $button );
    }

    /**
     * @Given on :pageSection I clicked at/on :button button
     * @When  on :pageSection I click at/on :button button
     *
     * Clicks the button identified by ':button', located in section ':section'
     */
    public function onPageSectionIClickAtButton( $button, $pageSection = null )
    {
        $base = $this->makeXpathForBlock( $pageSection );
        $el = $this->getXpath()->findButtons( $button, $base );
        EzAssertion::assertElementFound( $button, $el, $pageSection, 'button' );
        $el[0]->click();
    }

    /**
     * @Given I clicked on/at (the) :link link
     * @When  I click on/at (the) :link link
     *
     * Click a link with text ':link'
     */
    public function iClickAtLink( $link )
    {
        $this->onPageSectionIClickAtLink( $link );
    }

    /**
     * @Given on :pageSection I clicked on/at link link
     * @When  on :pageSection I click on/at :link link
     *
     * Click a link with text ':link' on page section ':pageSection'
     * Asserts that at least one link element is found.
     */
    public function onPageSectionIClickAtLink( $link, $pageSection = null )
    {
        $base = $this->makeXpathForBlock( $pageSection );
        $el = $this->getXpath()->findLinks( $link, $base );
        EzAssertion::assertElementFound( $link, $el, $pageSection, 'link' );
        $el[0]->click();
    }

    /**
     * @Given I checked :label checkbox
     * @When  I check :label checkbox
     *
     * Toggles the value for the checkbox with name ':label'
     */
    public function checkOption( $option )
    {
        $fieldElements = $this->getXpath()->findFields( $option );
        EzAssertion::assertElementFound( $option, $fieldElements, null, 'checkbox' );

        // this is needed for the cases where are checkboxes and radio's
        // side by side, for main option the radio and the extra being the
        // checkboxes values
        if ( strtolower( $fieldElements[0]->getAttribute( 'type' ) ) !== 'checkbox' )
        {
            $value = $fieldElements[0]->getAttribute( 'value' );
            $fieldElements = $this->getXpath()->findXpath( "//input[@type='checkbox' and @value='$value']" );
            EzAssertion::assertElementFound( $value, $fieldElements, null, 'checkbox' );
        }

        $fieldElements[0]->check();
    }

    /**
     * @When I select :option
     *
     * Selects option with value ':value'
     * IMPORTANT: Will thrown an error if more than 1 select/dropdown is found on page
     */
    public function iSelect( $option )
    {
        $elements = $this->getXpath()->findXpath( "//select" );
        Assertion::assertNotEmpty( $elements, "Unable to find a select field" );
        $elements[0]->selectOption( $option );
    }

    /**
     * @Given I selected :label radio button
     * @When  I select :label radio button
     *
     * Selects the radio button with label ':label'
     */
    public function iSelectRadioButton( $label )
    {
        $el = $this->getSession()->getPage()->findField( $label );
        Assertion::assertNotNull( $el, "Couldn't find a radio input with '$label'" );
        $el->check();
    }

     /**
     * @Given I filled form with:
     * @When  I fill form with:
     *
     * Fills a form with the provided field/value pairs:
     *      | field         | value                  |
     *      | Title         | A title text           |
     *      | Content       | Some content           |
     */
    public function iFillFormWith( TableNode $table )
    {
        foreach ( GherkinHelper::convertTableToArrayOfData( $table ) as $field => $value )
        {
            $elements = $this->getXpath()->findFields( $field );
            Assertion::assertNotEmpty( $elements, "Unable to find '{$field}' field" );
            $elements[0]->setValue( $value );
        }
    }

    /**
     * @Then I see field with value:
     *
     * Checks a form for the provided field/value pairs:
     *      | field         | value                  |
     *      | Title         | A title text           |
     *      | Content       | Some content           |
     */
    public function formHasValue( TableNode $table )
    {
        foreach ( GherkinHelper::convertTableToArrayOfData( $table ) as $field => $value )
        {
            $elements = $this->getXpath()->findFields( $field );
            Assertion::assertNotEmpty( $elements, "Unable to find '{$field}' field" );
            Assertion::assertEquals( $value, $elements[0]->getValue(), "Field values don't match" );
        }
    }

    /**
     * @Then I (should) see (the) (following) links:
     *
     * Asserts that links with the provided text can be found on the page.
     *      | link          |
     *      | some link     |
     *      ...
     *      | another link  |
     */
    public function iSeeLinks( TableNode $table )
    {
        $this->onPageSectionISeeLinks( $table );
    }

    /**
     * @Then on :pageSection I (should) see (the) (following) links:
     *
     */
    public function onPageSectionISeeLinks( TableNode $table, $pageSection = null )
    {
        $rows = $table->getRows();
        array_shift( $rows );

        foreach ( $rows as $row )
        {
            $link = $row[0];
            $el = $this->getXpath()->findLinks( $link, $this->makeXpathForBlock( $pageSection ) );

            Assertion::assertNotEmpty( $el, "Unexpected link found" );
        }
    }

    /**
     * @Then I shouldn't see (the) (following) links:
     * @Then I don't see (the) (following) links:
     *
     * Asserts that none of the links with the provided text can be found.
     */
    public function iDonTSeeLinks( TableNode $table )
    {
        $this->onPageSectionIDonTSeeLinks( 'main', $table );
    }

    /**
     * @Then on :pageSection I shouldn't see (the) (following) links:
     * @Then on :pageSection I don't see (the) (following) links:
     */
    public function onPageSectionIDonTSeeLinks( TableNode $table, $pageSection = null )
    {
        $rows = $table->getRows();
        array_shift( $rows );

        foreach ( $rows as $row )
        {
            $link = $row[0];
            $el = $this->getXpath()->findLinks( $link, $this->makeXpathForBlock( $pageSection ) );

            Assertion::assertEmpty( $el, "Unexpected link found" );
        }
    }

    /**
     * @Then I (should) see (the) links in the following order:
     * @Then I (should) see (the) links in this order:
     *
     * Checks if links exist, and appear in the specified order
     */
    public function iSeeLinksInFollowingOrder( TableNode $table )
    {
        // get all links
        $available = $this->getXpath()->findXpath( "//a[@href]" );

        $rows = $table->getRows();
        array_shift( $rows );

        // remove links from embeded arrays
        $links = array();
        foreach ( $rows as $row )
        {
            $links[] = $row[0];
        }

        // and finaly verify their existence
        $this->checkLinksExistence( $links, $available );
    }

    /**
     * @Then I (should) see (the) (following) links in:
     *
     * Example: this is used to see in tag cloud which tags have more results
     *      | link  | tag   |
     *      | link1 | title |
     *      | link2 | list  |
     *      | link3 | text  |
     */
    public function iSeeFollowingLinksIn( TableNode $table )
    {
        $session = $this->getSession();
        $rows = $table->getRows();
        array_shift( $rows );
        foreach ( $rows as $row )
        {
            Assertion::assertEquals( 2, count( $row ), "The table should be have array with link and tag" );

            // prepare XPath
            list( $link, $type ) = $row;
            $tags = $this->getTagsFor( $type );
            $xpaths = explode( '|', $this->getXpath()->makeElementXpath( 'link', $link ) );
            $xpath = implode(
                '|',
                array_map(
                    function( $tag ) use( $xpaths )
                    {
                        return "//$tag/" . implode( "| //$tag/", $xpaths );
                    },
                    $tags
                )
            );

            // search and do assertions
            $el = $this->getXpath()->findXpath( $xpath );
            EzAssertion::assertSingleElement( $link, $el, $type, 'link' );
        }
    }

    /**
     * @Then I (should) see :title title/topic
     *
     * Asserts that a (single) title element exists with the text ':title'
     */
    public function iSeeTitle( $title )
    {
        $literal = $this->getXpath()->literal( $title );
        $tags = $this->getTagsFor( "title" );
        $innerXpath = "[text() = {$literal} or .//*[text() = {$literal}]]";
        $xpathOptions = array_map(
            function( $tag ) use( $innerXpath )
            {
                return "//$tag$innerXpath";
            },
            $tags
        );

        $xpath = implode( '|', $xpathOptions );

        $el = $this->getXpath()->findXpath( $xpath );

        // assert that message was found
        EzAssertion::assertSingleElement( $title, $el, null, 'title' );
    }

    /**
     * @Then I (should) see table with:
     *
     * Asserts that a table exists with specified values.
     * The table header needs to have the number of the column to which the values belong,
     * all the other text is optional, normaly using 'Column' for easier understanding:
     *
     *      | Column 1 | Column 2 | Column 4 |
     *      | Value A  | Value B  | Value D  |
     *      ...
     *      | Value I  | Value J  | Value L  |
     */
    public function iSeeTableWith( TableNode $table )
    {
        $rows = $table->getRows();
        $headers = array_shift( $rows );

        $max = count( $headers );
        $mainHeader = array_shift( $headers );
        foreach ( $rows as $row )
        {
            $mainColumn = array_shift( $row );
            $foundRows = $this->getTableRow( $mainColumn, $mainHeader );

            $found = false;
            $maxFound = count( $foundRows );
            for ( $i = 0; $i < $maxFound && !$found; $i++ )
            {
                if ( $this->existTableRow( $foundRows[$i], $row, $headers ) )
                {
                    $found = true;
                }
            }

            $message = "Couldn't find row with elements: '" . implode( ",", array_merge( array( $mainColumn ), $row ) ) . "'";
            Assertion::assertTrue( $found, $message );
        }
    }

    /**
     * @Then I (should) see :text text emphasized
     *
     * Checks that an element exists on the page with text ':text' emphasized.
     */
    public function iSeeTextEmphasized( $text )
    {
        $this->onPageSectionISeeTextEmphasized( $text );
    }

    /**
     * @Then on :pageSection I (should) see the :text text emphasized
     */
    public function onPageSectionISeeTextEmphasized( $text, $pageSection = null )
    {
        // first find the text
        $base = $this->makeXpathForBlock( $pageSection );
        $el = $this->getXpath()->findXpath( "$base//*[contains( text(), {$this->getXpath()->literal( $text )} )]" );

        EzAssertion::assertSingleElement( $text, $el, $pageSection, 'emphasized text' );

        // finally verify if it has custom characteristics
        Assertion::assertTrue(
            $this->isElementEmphasized( $el[0] ),
            "The text '$text' isn't emphasized"
        );
    }

    /**
     * @Then I (should) see :message warning/error
     *
     * Checks that an element with the class 'warning' or 'error' exists with text ':message'
     */
    public function iSeeWarning( $message )
    {
        $el = $this->getXpath()->findXpath(
            "//*[contains( @class, 'warning' ) or contains( @class, 'error' )]"
            . "//*[text() = {$this->getXpath()->literal( $message )}]"
        );

        Assertion::assertNotNull( $el, "Couldn't find error/warning message '{$message}'" );
    }

    /**
     * @Then I (should) see the exact :text: message/text
     *
     * Checks that an element on the page contains the exact text ':text'
     */
    public function iSeeText( $text )
    {
        $this->onPageSectionISeeText( $text );
    }

    /**
     * @Then on :pageSection I (should) see the exact :text message/text
     */
    public function onPageSectionISeeText( $text, $pageSection = null )
    {
        $base = $this->makeXpathForBlock( $pageSection );

        $literal = $this->getXpath()->literal( $text );
        $el = $this->getXpath()->findXpath( "$base//*[contains( text(), $literal )]" );

        Assertion::assertNotNull( $el, "Couldn't find '$text' text" );
        Assertion::assertEquals( trim( $el->getText() ), $text, "Couldn't find '$text' text" );
    }

    /**
     * @Then I (should) see :message message/text
     *
     * Checks that current page contains text.
     */
    public function iSeeMessage( $text )
    {
        $this->checkForExceptions();
        $this->assertSession()->pageTextContains( $text );
    }

    /**
     * @Then I don't see :text message/text
     *
     * Checks that current page does not contain text.
     */
    public function iDonTSeeMessage( $text )
    {
        $this->assertSession()->pageTextNotContains( $text );
    }

    /**
     * @Then the checkbox :label should be checked
     */
    public function isCheckedOption( $label )
    {
        $isChecked = $this->getCheckboxChecked( $label );
        Assertion::assertTrue( $isChecked, "Checkbox $label is not checked" );
    }

    /**
     * @Then the checkbox :label should not be checked
     */
    public function isNotCheckedOption( $label )
    {
        $isChecked = $this->getCheckboxChecked( $label );
        Assertion::assertFalse( $isChecked, "Checkbox $label is checked" );
    }

    /**
     * Helper for checkbox
     */
    private function getCheckboxChecked( $option )
    {
        $fieldElements = $this->getXpath()->findFields( $option );
        EzAssertion::assertElementFound( $option, $fieldElements, null, 'checkbox' );

        // this is needed for the cases where are checkboxes and radio's
        // side by side, for main option the radio and the extra being the
        // checkboxes values
        if ( strtolower( $fieldElements[0]->getAttribute( 'type' ) ) !== 'checkbox' )
        {
            $value = $fieldElements[0]->getAttribute( 'value' );
            $fieldElements = $this->getXpath()->findXpath( "//input[@type='checkbox' and @value='$value']" );
            EzAssertion::assertElementFound( $value, $fieldElements, null, 'checkbox' );
        }

        return $isChecked = ( $fieldElements[0]->getAttribute( 'checked' ) ) === 'true';
    }
}
