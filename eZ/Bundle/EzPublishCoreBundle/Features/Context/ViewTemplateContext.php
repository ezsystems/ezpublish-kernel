<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\RawMinkContext;
use PHPUnit_Framework_Assert as Assertion;

/**
 * Used to describe expectations in the context of view templates.
 */
class ViewTemplateContext extends RawMinkContext implements Context, SnippetAcceptingContext
{
    /**
     * @Then /^the view template has an? \'([^\']*)\' variable$/
     */
    public function theViewTemplateHasAVariable($variableName)
    {
        $this->assertSession()->elementExists('xpath', '//' . $this->buildVariableXpath($variableName));
    }

    /**
     * @Given /^the \'([^\']*)\' variable is an array with the results from the queryType$/
     */
    public function theVariableIsAnArrayWithTheResultsFrom($variableName, $queryTypeName)
    {
        $xpath = '//' . $this->buildVariableXpath($variableName);
        $elements = $this->getSession()->getPage()->findAll('xpath', $xpath);
        /** @var NodeElement $element */
        $element = $elements[0];
        Assertion::assertEquals(
            $element->find('xpath', '/following-sibling::abbr')->getText(),
            'array:1'
        );
    }

    private function buildVariableXpath($variableName)
    {
        return "span[@class='sf-dump-key' and text() = '$variableName']";
    }
}
