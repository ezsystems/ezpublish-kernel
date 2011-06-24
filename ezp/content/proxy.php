<?php

namespace ezp\content

/**
 * Proxy class for content model objects
 */
class Proxy extends \ezp\base\AbstractModel implements \ezp\ProxyInterface
{
    /**
     * Service used to load the object the proxy represents
     *
     * @var Services/ServiceInterface
     */
    protected $service;

    /**
     * Id of the object
     *
     * @var int
     */
    protected $id;

    /**
     * Method to use on the service to load the object
     *
     * @var string
     */
    protected $method;

    /**
     * Constructs the Proxy object for content model objects
     *
     * @param Services/ServiceInterface $service
     * @param int $id
     * @param string $method
     */
    public function __construct( Services\ServiceInterface $service, $id, $method = "load" )
    {
        $this->readableProperties = array( "id" => true );
        $this->id = $id;
        $this->method = $load;
        $this->service = $service;
    }

    public function load()
    {
        $fn = $this->method;
        return $this->service->$fn( $this->id );
    }


}

?>
