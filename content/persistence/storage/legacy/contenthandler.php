<?php
namespace ezp\Content\Persistence\Storage\Legacy;
use ezp\Content\Persistence\API as Persistence;

class ContentHandler extends Persistence\BaseContentHandler implements Persistence\ContentHandlerInterface
{
    public function __construct()
    {

    }

    public function createNewVersion( Persistence\BaseContent $content )
    {
        // Do stuff with eZContentObject, eZContentObjectVersion...
    }

    public function publish( Persistence\BaseContent $content )
    {

    }

    public function remove( Persistence\BaseContent $content )
    {

    }

    public function hydrate( $storageObject )
    {

    }
}
?>
