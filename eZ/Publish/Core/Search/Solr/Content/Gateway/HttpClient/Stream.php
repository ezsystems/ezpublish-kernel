<?php

/**
 * File containing the Stream HttpClient.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Gateway\HttpClient;

use eZ\Publish\Core\Search\Solr\Content\Gateway\HttpClient;
use eZ\Publish\Core\Search\Solr\Content\Gateway\Message;
use eZ\Publish\Core\Search\Solr\Content\Gateway\Endpoint;

/**
 * Simple PHP stream based HTTP client.
 */
class Stream implements HttpClient
{
    /**
     * Execute a HTTP request to the remote server.
     *
     * Returns the result from the remote server.
     *
     * @param string $method
     * @param \eZ\Publish\Core\Search\Solr\Content\Gateway\Endpoint $endpoint
     * @param string $path
     * @param Message $message
     *
     * @return Message
     */
    public function request($method, Endpoint $endpoint, $path, Message $message = null)
    {
        $message = $message ?: new Message();
        $requestHeaders = $this->getRequestHeaders($message, $endpoint);
        $contextOptions = array(
            'http' => array(
                'method' => $method,
                'content' => $message->body,
                'ignore_errors' => true,
                'header' => $requestHeaders,
            ),
        );

        $httpFilePointer = @fopen(
            $endpoint->getURL() . $path,
            'r',
            false,
            stream_context_create($contextOptions)
        );

        // Check if connection has been established successfully
        if ($httpFilePointer === false) {
            throw new ConnectionException($endpoint->getURL(), $path, $method);
        }

        // Read request body
        $body = '';
        while (!feof($httpFilePointer)) {
            $body .= fgets($httpFilePointer);
        }

        $metaData = stream_get_meta_data($httpFilePointer);
        // This depends on PHP compiled with or without --curl-enable-streamwrappers
        $rawHeaders = isset($metaData['wrapper_data']['headers']) ?
            $metaData['wrapper_data']['headers'] :
            $metaData['wrapper_data'];
        $headers = array();

        foreach ($rawHeaders as $lineContent) {
            // Extract header values
            if (preg_match('(^HTTP/(?P<version>\d+\.\d+)\s+(?P<status>\d+))S', $lineContent, $match)) {
                $headers['version'] = $match['version'];
                $headers['status'] = (int)$match['status'];
            } else {
                list($key, $value) = explode(':', $lineContent, 2);
                $headers[$key] = ltrim($value);
            }
        }

        return new Message(
            $headers,
            $body
        );
    }

    /**
     * Get formatted request headers.
     *
     * Merged with the default values.
     *
     * @param \eZ\Publish\Core\Search\Solr\Content\Gateway\Message $message
     * @param \eZ\Publish\Core\Search\Solr\Content\Gateway\Endpoint $endpoint
     *
     * @return string
     */
    protected function getRequestHeaders(Message $message, Endpoint $endpoint)
    {
        // Use message headers as default
        $headers = $message->headers;

        // Set headers from $endpoint
        if ($endpoint->user !== null) {
            $headers['Authorization'] = 'Basic ' . base64_encode("{$endpoint->user}:{$endpoint->pass}");
        }

        // Render headers
        $requestHeaders = '';

        foreach ($headers as $name => $value) {
            if (is_numeric($name)) {
                throw new \RuntimeException("Invalid HTTP header name $name");
            }

            $requestHeaders .= "$name: $value\r\n";
        }

        return $requestHeaders;
    }
}
