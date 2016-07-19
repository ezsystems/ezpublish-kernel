<?php

/**
 * File containing the ContentFieldValidationException ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\Values\Translation;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * ContentFieldValidationException value object visitor.
 */
class ContentFieldValidationException extends BadRequestException
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Exceptions\ContentFieldValidationException $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ErrorMessage');

        $statusCode = $this->getStatus();
        $visitor->setStatus($statusCode);
        $visitor->setHeader('Content-Type', $generator->getMediaType('ErrorMessage'));

        $generator->startValueElement('errorCode', $statusCode);
        $generator->endValueElement('errorCode');

        $generator->startValueElement('errorMessage', $this->httpStatusCodes[$statusCode]);
        $generator->endValueElement('errorMessage');

        $generator->startValueElement('errorDescription', $data->getMessage());
        $generator->endValueElement('errorDescription');

        $generator->startHashElement('errorDetails');
        $generator->startList('fields');
        foreach ($data->getFieldErrors() as $fieldTypeId => $translations) {
            foreach ($translations as $languageCode => $validationErrors) {
                if (!is_array($validationErrors)) {
                    $validationErrors = [$validationErrors];
                }

                foreach ($validationErrors as $validationError) {
                    $generator->startHashElement('field');
                    $generator->startAttribute('fieldTypeId', $fieldTypeId);
                    $generator->endAttribute('fieldTypeId');

                    $generator->startList('errors');
                    $generator->startHashElement('error');

                    $generator->startValueElement('type', $validationError->getTarget());
                    $generator->endValueElement('type');

                    $translation = $validationError->getTranslatableMessage();
                    $generator->startValueElement(
                        'message',
                        $this->translator->trans(
                            $this->translationToString($translation),
                            $translation->values,
                            'repository_exceptions'
                        )
                    );
                    $generator->endValueElement('message');

                    $generator->endHashElement('error');
                    $generator->endList('errors');
                    $generator->endHashElement('field');
                }
            }
        }
        $generator->endList('fields');
        $generator->endHashElement('errorDetails');

        if ($this->debug) {
            $generator->startValueElement('trace', $data->getTraceAsString());
            $generator->endValueElement('trace');

            $generator->startValueElement('file', $data->getFile());
            $generator->endValueElement('file');

            $generator->startValueElement('line', $data->getLine());
            $generator->endValueElement('line');
        }

        $generator->endObjectElement('ErrorMessage');
    }

    /**
     * Convert a Translation object to a string, detecting singular/plural as needed.
     *
     * @param Translation $translation The Translation object
     * @return string
     */
    private function translationToString(Translation $translation)
    {
        $values = $translation->values;
        if ($translation instanceof Translation\Plural) {
            if (current($values) === 1) {
                return $translation->singular;
            } else {
                return $translation->plural;
            }
        } else {
            return $translation->message;
        }
    }
}
