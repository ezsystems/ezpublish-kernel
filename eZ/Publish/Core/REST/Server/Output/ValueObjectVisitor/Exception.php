<?php

/**
 * File containing the Exception ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\Base\Translatable;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Exception value object visitor.
 */
class Exception extends ValueObjectVisitor
{
    /**
     * Is debug mode enabled?
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Mapping of HTTP status codes to their respective error messages.
     *
     * @var array
     */
    protected $httpStatusCodes = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => "I'm a teapot",
        421 => 'There are too many connections from your internet address',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
    ];

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * Construct from debug flag.
     *
     * @param bool $debug
     * @param TranslatorInterface $translator
     */
    public function __construct($debug = false, TranslatorInterface $translator = null)
    {
        $this->debug = (bool)$debug;
        $this->translator = $translator;
    }

    /**
     * Returns HTTP status code.
     *
     * @return int
     */
    protected function getStatus()
    {
        return 500;
    }

    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \Exception $data
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

        if ($data instanceof Translatable && $this->translator) {
            /** @Ignore */
            $errorDescription = $this->translator->trans($data->getMessageTemplate(), $data->getParameters(), 'repository_exceptions');
        } else {
            $errorDescription = $data->getMessage();
        }
        $generator->startValueElement('errorDescription', $errorDescription);
        $generator->endValueElement('errorDescription');

        if ($this->debug) {
            $generator->startValueElement('trace', $data->getTraceAsString());
            $generator->endValueElement('trace');

            $generator->startValueElement('file', $data->getFile());
            $generator->endValueElement('file');

            $generator->startValueElement('line', $data->getLine());
            $generator->endValueElement('line');
        }

        if ($previous = $data->getPrevious()) {
            $generator->startObjectElement('Previous', 'ErrorMessage');
            $visitor->visitValueObject($previous);
            $generator->endObjectElement('Previous');
        }

        $generator->endObjectElement('ErrorMessage');
    }
}
