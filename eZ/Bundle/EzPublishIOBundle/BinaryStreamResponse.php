<?php

/**
 * This file is part of the eZ Publish Legacy package.
 *
 * Many parts are copied from the Symfony2 kernel, and are copyrighted to their respective owners.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle;

use DateTime;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\IO\Values\BinaryFile;
use LogicException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A modified version of HttpFoundation's BinaryFileResponse that accepts a stream as the input.
 */
class BinaryStreamResponse extends Response
{
    protected static $trustXSendfileTypeHeader = false;

    /** @var BinaryFile */
    protected $file;

    /** @var IOServiceInterface */
    protected $ioService;

    protected $offset;

    protected $maxlen;

    /**
     * Constructor.
     *
     * @param BinaryFile          $binaryFile         The name of the file to stream
     * @param IOServiceInterface  $ioService          The name of the file to stream
     * @param int                 $status             The response status code
     * @param array               $headers            An array of response headers
     * @param bool                $public             Files are public by default
     * @param null|string         $contentDisposition The type of Content-Disposition to set automatically with the filename
     * @param bool                $autoEtag           Whether the ETag header should be automatically set
     * @param bool                $autoLastModified   Whether the Last-Modified header should be automatically set
     */
    public function __construct(BinaryFile $binaryFile, IOServiceInterface $ioService, $status = 200, $headers = [], $public = true, $contentDisposition = null, $autoLastModified = true)
    {
        $this->ioService = $ioService;

        parent::__construct(null, $status, $headers);

        $this->setFile($binaryFile, $contentDisposition, $autoLastModified);

        if ($public) {
            $this->setPublic();
        }
    }

    /**
     * Sets the file to stream.
     *
     * @param \SplFileInfo|string $file The file to stream
     * @param string $contentDisposition
     * @param bool $autoEtag
     * @param bool $autoLastModified
     *
     * @return BinaryFileResponse
     */
    public function setFile($file, $contentDisposition = null, $autoLastModified = true)
    {
        $this->file = $file;

        if ($autoLastModified) {
            $this->setAutoLastModified();
        }

        if ($contentDisposition) {
            $this->setContentDisposition($contentDisposition);
        }

        return $this;
    }

    /**
     * Gets the file.
     *
     * @return BinaryFile The file to stream
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Automatically sets the Last-Modified header according the file modification date.
     */
    public function setAutoLastModified()
    {
        $this->setLastModified(DateTime::createFromFormat('U', $this->file->mtime->getTimestamp()));

        return $this;
    }

    /**
     * Sets the Content-Disposition header with the given filename.
     *
     * @param string $disposition ResponseHeaderBag::DISPOSITION_INLINE or ResponseHeaderBag::DISPOSITION_ATTACHMENT
     * @param string $filename Optionally use this filename instead of the real name of the file
     * @param string $filenameFallback A fallback filename, containing only ASCII characters. Defaults to an automatically encoded filename
     *
     * @return BinaryStreamResponse
     */
    public function setContentDisposition($disposition, $filename = '', $filenameFallback = '')
    {
        if ($filename === '') {
            $filename = $this->file->id;
        }

        if (empty($filenameFallback)) {
            $filenameFallback = mb_convert_encoding($filename, 'ASCII');
        }

        $dispositionHeader = $this->headers->makeDisposition($disposition, $filename, $filenameFallback);
        $this->headers->set('Content-Disposition', $dispositionHeader);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(Request $request)
    {
        $this->headers->set('Content-Length', $this->file->size);
        $this->headers->set('Accept-Ranges', 'bytes');
        $this->headers->set('Content-Transfer-Encoding', 'binary');

        if (!$this->headers->has('Content-Type')) {
            $this->headers->set(
                'Content-Type',
                $this->ioService->getMimeType($this->file->id) ?: 'application/octet-stream'
            );
        }

        if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
            $this->setProtocolVersion('1.1');
        }

        $this->ensureIEOverSSLCompatibility($request);

        $this->offset = 0;
        $this->maxlen = -1;

        if ($request->headers->has('Range')) {
            // Process the range headers.
            if (!$request->headers->has('If-Range') || $this->getEtag() == $request->headers->get('If-Range')) {
                $range = $request->headers->get('Range');
                $fileSize = $this->file->size;

                list($start, $end) = explode('-', substr($range, 6), 2) + [0];

                $end = ('' === $end) ? $fileSize - 1 : (int)$end;

                if ('' === $start) {
                    $start = $fileSize - $end;
                    $end = $fileSize - 1;
                } else {
                    $start = (int)$start;
                }

                if ($start <= $end) {
                    if ($start < 0 || $end > $fileSize - 1) {
                        $this->setStatusCode(416); // HTTP_REQUESTED_RANGE_NOT_SATISFIABLE
                    } elseif ($start !== 0 || $end !== $fileSize - 1) {
                        $this->maxlen = $end < $fileSize ? $end - $start + 1 : -1;
                        $this->offset = $start;

                        $this->setStatusCode(206); // HTTP_PARTIAL_CONTENT
                        $this->headers->set('Content-Range', sprintf('bytes %s-%s/%s', $start, $end, $fileSize));
                        $this->headers->set('Content-Length', $end - $start + 1);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Sends the file.
     */
    public function sendContent()
    {
        if (!$this->isSuccessful()) {
            parent::sendContent();

            return;
        }

        if (0 === $this->maxlen) {
            return;
        }

        $out = fopen('php://output', 'wb');
        $in = $this->ioService->getFileInputStream($this->file);
        stream_copy_to_stream($in, $out, $this->maxlen, $this->offset);

        fclose($out);
    }

    /**
     * {@inheritdoc}
     *
     * @throws LogicException when the content is not null
     */
    public function setContent($content)
    {
        if (null !== $content) {
            throw new LogicException('The content cannot be set on a BinaryStreamResponse instance.');
        }
    }

    public function getContent()
    {
        return null;
    }
}
