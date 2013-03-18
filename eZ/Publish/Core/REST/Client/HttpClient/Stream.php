<?php
/**
 * File containing the HttpClient interface
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\HttpClient;

use eZ\Publish\Core\REST\Client\HttpClient;
use eZ\Publish\Core\REST\Common\Message;

/**
 * Simple PHP stream based HTTP client.
 */
class Stream implements HttpClient
{
    /**
     * Optional default headers for each request.
     *
     * @var array
     */
    private $headers = array();

    /**
     * The remote REST server location.
     *
     * @var string
     */
    private $server;

    /**
     * Constructs a new REST client instance for the given <b>$server</b>.
     *
     * @param string $server Remote server location. Must include the used protocol.
     */
    public function __construct( $server )
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

        if ( $url['user'] || $url['pass'] )
        {
            $this->headers['Authorization'] = 'Basic ' . base64_encode( "{$url['user']}:{$url['pass']}" );
        }

        $this->server = $url['scheme'] . '://' . $url['host'];
        if ( $url['port'] )
        {
            $this->server .= ':' . $url['port'];
        }
        $this->server .= $url['path'];
    }

    /**
     * Execute a HTTP request to the remote server
     *
     * Returns the result from the remote server.
     *
     * @param string $method
     * @param string $path
     * @param \eZ\Publish\Core\REST\Common\Message $message
     *
     * @return \eZ\Publish\Core\REST\Common\Message
     */
    public function request( $method, $path, Message $message = null )
    {
        $message = $message ?: new Message();

        $requestHeaders = $this->getRequestHeaders( $message->headers );

        $url = $this->server . $path;

        $contextOptions = array(
            'http' => array(
                'method'        => $method,
                'content'       => $message->body,
                'ignore_errors' => true,
                'header'        => $requestHeaders,
                // Do not follow redirects, since we want to handle them
                // explicitly.
                'follow_location' => 0,
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
            throw new ConnectionException( $this->server, $path, $method );
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
            $body,
            $headers['status']
        );
    }

    /**
     * Get formatted request headers
     *
     * Merged with the default values.
     *
     * @param array $headers
     *
     * @return string
     */
    protected function getRequestHeaders( array $headers )
    {
        $requestHeaders = '';

        foreach ( $this->headers as $name => $value )
        {
            if ( !isset( $headers[$name] ) )
            {
                $requestHeaders .= "$name: $value\r\n";
            }
        }

        foreach ( $headers as $name => $value )
        {
            $requestHeaders .= "$name: $value\r\n";
        }

        return $requestHeaders;
    }
}
