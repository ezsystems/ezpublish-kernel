<?php

namespace ezp;

/**
 * Interface for the Proxy object
 */
interface ProxyInterface
{
    /**
     * Loads and returns the object the Proxy object represents
     *
     * @return \ezp\DomainObjectInterface
     */
    public function load();
}

?>
