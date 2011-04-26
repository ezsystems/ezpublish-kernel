<?php
namespace ezp\Content\Persistence\Storage\Legacy;
use ezp\Content\Persistence\API as Persistence;

class Content extends Persistence\BaseContent implements Persistence\ContentInterface
{
    /**
     * @var \eZContentObject
     */
    public $contentObject;

    public function hydrate( $storageObject )
    {
        if ( !$storageObject instanceof \eZContentObject )
        {
            throw new \InvalidArgumentException( __METHOD__ . " - Provided storage object is not an instance of eZContentObject" );
        }

        $this->contentObject = $storageObject;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentInterface::getOwner()
     */
    public function getOwner()
    {

    }

    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentInterface::getSection()
     */
    public function getSection()
    {

    }

    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentInterface::getType()
     */
    public function getType()
    {

    }

    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentInterface::getRelations()
     */
    public function getRelations()
    {
        $handler = new ContentRelationHandler();
        $aRelations = $handler->extractRelations( $this );

        return $aRelations;
    }

    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentInterface::getVersions()
     */
    public function getVersions()
    {

    }

    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentInterface::getCurrentVersion()
     */
    public function getCurrentVersion()
    {

    }

    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentInterface::getLocations()
     */
    public function getLocations()
    {

    }

    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentInterface::getMainLocation()
     */
    public function getMainLocation()
    {

    }
}
?>
