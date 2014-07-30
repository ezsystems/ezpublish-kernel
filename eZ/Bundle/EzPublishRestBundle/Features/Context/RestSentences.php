<?php
/**
 * File containing the RestSentences interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;

/**
 * RestInternalSentences
 *
 * This interface contains the BDD sentences to use internally on rest testing
 */
interface RestSentences
{
    /**
     * @When /^I create a "(?P<requestType>[^"]*)" request to "(?P<resourceUrl>[^"]*)"$/
     */
    public function iCreateRequest( $requestType, $resourceUrl );

    /**
     * @When /^I send a "(?P<requestType>[^"]*)" request to "(?P<resourceUrl>[^"]*)"$/
     */
    public function iCreateAndSendRequest( $requestType, $resourceUrl );

    /**
     * @When /^I add "(?P<header>[^"]*)" header (?:to|with) "(?P<action>[^"]*)" (?:an|a|for|to|the|of) "(?P<object>[^"]*)"$/
     *
     * Sentences examples:
     *  - I add content-type header to "Create" an "ContentType"
     *  - I add content-type header to "List" the "View
     *
     * Result example:
     *      Content-type: <header-prefix><object><action>+<content-type>
     *      Content-type: application/vnd.ez.api.ContentTypeGroupInput+xml
     *
     * Header can be:
     *  - accept
     *  - content-type
     */
    public function iAddHeaderToObjectAction( $header, $action, $object );

    /**
     * @When /^I add "(?P<header>[^"]*)" header (?:for|with) (?:an|a|to|the|of) "(?P<object>[^"]*)"$/
     *
     * Sentences examples:
     *  - I add accept header for "ContentType"
     *
     * Result example:
     *      Accept: <header-prefix><object>+<content-type>
     *      Accept: application/vnd.ez.api.ContentTypeGroup+xml
     *
     * Header can be:
     *  - accept
     *  - content-type
     */
    public function iAddHeaderForObject( $header, $object );

    /**
     * @When /^I make (?:an |a |)"(?P<objectType>[^"]*)" object$/
     *
     * This will create an object of the type passed for step by step be filled
     */
    public function iMakeAnObject( $objectType );

    /**
     * @When /^I add (?:the |)"(?P<value>[^"]*)" value to "(?P<field>[^"]*)" field$/
     */
    public function iAddValueToField( $value, $field );

    /**
     * @When /^I add "(?P<header>[^"]*)" header with "(?P<value>[^"]*)" value$/
     */
    public function iAddHeaderWithValue( $header, $value );

    /**
     * @When /^I add headers(?:\:|)$/
     */
    public function iAddHeaders( TableNode $table );

    /**
     * @When /^I send (?:the |)request$/
     */
    public function iSendRequest();

    /**
     * @Then /^I see (?P<statusCode>\d{3}) status code$/
     */
    public function iSeeResponseStatusCode( $statusCode );

    /**
     * @Then /^I see "(?P<statusMessage>[^"]*)" status (?:reason phrase|message)$/
     */
    public function iSeeResponseStatusMessage( $statusMessage );

    /**
     * @Then /^I see "(?P<header>[^"]*)" header with "(?P<value>[^"]*)" value$/
     */
    public function iSeeResponseHeaderWithValue( $header, $value );

    /**
     * @Then /^I see "(?P<header>[^"]*)" header to "(?P<action>[^"]*)" (?:an|a|for|to|the) "(?P<object>[^"]*)"$/
     */
    public function iSeeResponseHeaderToObjectAction( $header, $action, $object );

    /**
     * @Then /^I see "(?P<header>[^"]*)" header (?:for|with) (?:an|a|to|the) "(?P<object>[^"]*)"$/
     */
    public function iSeeResponseHeaderForObject( $header, $object );

    /**
     * @Then /^I see "(?P<header>[^"]*)" header$/
     */
    public function iSeeResponseHeader( $header );

    /**
     * @When /^I see headers(?:\:|)$/
     */
    public function iSeeResponseHeaders( TableNode $table );

    /**
     * @When /^I only see headers(?:\:|)$/
     */
    public function iOnlySeeResponseHeaders( TableNode $table );

    /**
     * @Then /^I (?:don\'t|do not) see "(?P<header>[^"]*)" header$/
     */
    public function iDonTSeeResponseHeader( $header );

    /**
     * @Then /^I (?:don\'t|do not) see "(?P<header>[^"]*)" header with "(?P<value>[^"]*)" value$/
     */
    public function iDonTSeeResponseHeaderWithValue( $header, $value );

    /**
     * @Then /^I see body with(?:\:|)$/
     *       """
     *          data
     *       """
     */
    public function iSeeResponseBodyWith( PyStringNode $body );

    /**
     * @Then /^I see response body with "(?P<object>[^"]*)" object$/
     *
     * @param string $object Object should be "ContentType" or "UserCreate", ....
     */
    public function iSeeResponseBodyWithObject( $object );

    /**
     * @Then /^I see body with "(?P<value>[^"]*)" value$/
     */
    public function iSeeResponseBodyWithValue( $value );

    /**
     * @Then /^I see response object field "(?P<field>[^"]*)" with "(?P<value>[^"]*)" value$/
     */
    public function iSeeResponseObjectWithFieldValue( $field, $value );

    /**
     * @Then /^I see response error description with "(?P<errorDescriptionRegEx>[^"]*)"$/
     */
    public function iSeeResponseErrorWithDescription( $errorDescriptionRegEx );

    /**
     * @Then /^I see response error (?P<statusCode>\d{3}) status code$/
     */
    public function iSeeResponseErrorStatusCode( $statusCode );
}
