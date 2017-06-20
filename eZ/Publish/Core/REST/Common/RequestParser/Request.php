<?php

namespace eZ\Publish\Core\REST\Common\RequestParser;

class Request
{
    /**
     * @var mixed[string]
     */
    public $variables;

    /**
     * @var string
     */
    public $type;

    /**
     * @param string $type
     * @param mixed[string] $variables
     */
    public function __construct($type, array $variables)
    {
        $this->type = $type;
        $this->variables = $variables;
    }
}
