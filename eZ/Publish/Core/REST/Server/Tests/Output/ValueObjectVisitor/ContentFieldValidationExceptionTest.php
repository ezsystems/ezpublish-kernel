<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException as CoreContentFieldValidationException;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Exceptions;
use Symfony\Component\Translation\Translator;

class ContentFieldValidationExceptionTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentFieldValidationException visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $exception = $this->getException();

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $exception
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains ErrorMessage element and description.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsErrorDescription($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'errorDescription',
                'content' => $this->getExpectedDescription(),
            ],
            $result,
            'Missing <errorDescription> element.'
        );
    }

    /**
     * Test if result contains ErrorMessage element and details.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsErrorDetails($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'errorDetails',
            ],
            $result,
            'Missing <errorDetails> element.'
        );

        $this->assertXMLTag(
            [
                'tag' => 'field',
            ],
            $result,
            'Missing <field> element.'
        );
    }

    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode()
    {
        return 400;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage()
    {
        return 'Bad Request';
    }

    /**
     * Get expected description.
     *
     * @return string
     */
    protected function getExpectedDescription()
    {
        return 'Content fields did not validate';
    }

    /**
     * Gets the exception.
     *
     * @return \Exception
     */
    protected function getException()
    {
        return new Exceptions\ContentFieldValidationException(
            new CoreContentFieldValidationException([
                1 => [
                    'eng-GB' => new ValidationError(
                        "Value for required field definition '%identifier%' with language '%languageCode%' is empty",
                        null,
                        ['%identifier%' => 'name', '%languageCode%' => 'eng-GB'],
                        'empty'
                    ),
                ],
                2 => [
                    'eng-GB' => new ValidationError(
                        'The value must be a valid email address.',
                        null,
                        [],
                        'email'
                    ),
                ],
            ])
        );
    }

    /**
     * Gets the exception visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ContentFieldValidationException
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\ContentFieldValidationException(false, new Translator('eng-GB'));
    }
}
