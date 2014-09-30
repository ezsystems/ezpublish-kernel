<?php

namespace eZ\Bundle\EzPublishDFSBundle;

use eZ\Bundle\EzPublishDFSBundle\DependencyInjection\EzPublishDFSExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EzPublishDFSBundle extends Bundle
{
    public function getContainerExtension()
    {
        if ( !isset( $this->extension ) )
        {
            $this->extension = new EzPublishDFSExtension();
        }

        return $this->extension;
    }

}
