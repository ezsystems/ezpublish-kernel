<?php
/**
 * File containing the Stream HttpClient
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Gateway\HttpClient;

use eZ\Publish\Core\Search\Solr\Content\Gateway\HttpClient;
use eZ\Publish\Core\Search\Solr\Content\Gateway\Message;

/**
 * Simple PHP stream based HTTP client.
 */
class Stream implements HttpClient
{
    /**
     * Execute a HTTP request to the remote server
     *
     * Returns the result from the remote server.
     *
     * @param string $method
     * @param string $server
     * @param string $path
     * @param Message $message
     *
     * @return Message
     */
    public function request( $method, $server, $path, Message $message = null )
    {
        $url = parse_url( rtrim( $server, '/' ) );
        $url += array(
            'scheme' => 'http',
            'host'   => null,
            'port'   => null,
            'user'   => null,
            'pass'   => null,
            'path'   => null,
        );

        $headers = array();
        if ( $url['user'] || $url['pass'] )
        {
            $headers['Authorization'] = 'Basic ' . base64_encode( "{$url['user']}:{$url['pass']}" );
        }

        $server = $url['scheme'] . '://' . $url['host'];
        if ( $url['port'] )
        {
            $server .= ':' . $url['port'];
        }
        $server .= $url['path'];

        $message = $message ?: new Message();

        $requestHeaders = $this->getRequestHeaders( $message->headers, $headers );

        $url = $server . $path;

        $contextOptions = array(
            'http' => array(
                'method'        => $method,
                'content'       => $message->body,
                'ignore_errors' => true,
                'header'        => $requestHeaders,
            ),
        );

        $httpFilePointer = @fopen(
            $url,
            'r',
            false,
            stream_context_create( $contextOptions )
        );

        // Check if connection has been established successfully
        if ( $httpFilePointer === false )
        {
            throw new ConnectionException( $server, $path, $method );
        }

        // Read request body
        $body = '';
        while ( !feof( $httpFilePointer ) )
        {
            $body .= fgets( $httpFilePointer );
        }

        $metaData   = stream_get_meta_data( $httpFilePointer );
        // This depends on PHP compiled with or without --curl-enable-streamwrappers
        $rawHeaders = isset( $metaData['wrapper_data']['headers'] ) ?
            $metaData['wrapper_data']['headers'] :
            $metaData['wrapper_data'];
        $headers    = array();

        foreach ( $rawHeaders as $lineContent )
        {
            // Extract header values
            if ( preg_match( '(^HTTP/(?P<version>\d+\.\d+)\s+(?P<status>\d+))S', $lineContent, $match ) )
            {
                $headers['version'] = $match['version'];
                $headers['status']  = (int)$match['status'];
            }
            else
            {
                list( $key, $value ) = explode( ':', $lineContent, 2 );
                $headers[$key] = ltrim( $value );
            }
        }

        return new Message(
            $headers,
            $body
        );
    }

    /**
     * Get formatted request headers
     *
     * Merged with the default values.
     *
     * @param array $headers1
     * @param array $headers2
     *
     * @return string
     */
    protected function getRequestHeaders( array $headers1, array $headers2 )
    {
        $requestHeaders = '';

        foreach ( $headers2 as $name => $value )
        {
            if ( !isset( $headers1[$name] ) )
            {
                $requestHeaders .= "$name: $value\r\n";
            }
        }

        foreach ( $headers1 as $name => $value )
        {
            if ( is_numeric( $name ) )
            {
                throw new \RuntimeException( "Invalid HTTP header name $name" );
            }

            $requestHeaders .= "$name: $value\r\n";
        }

        return $requestHeaders;
    }
}
