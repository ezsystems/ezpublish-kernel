<?php
namespace ezp\Content\Persistence\Storage\Legacy;
use ezp\Content\Persistence\API as Persistence;

class ContentRelation extends Persistence\BaseContentRelation implements Persistence\ContentRelationInterface
{
    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentRelationInterface::getRelationType()
     */
    public function getRelationType()
    {
        return $this->relationType;
    }

    public function setRelationType( $relationType )
    {
        $this->relationType = $relationType;
    }

    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentRelationInterface::getContentFrom()
     */
    public function getContentFrom()
    {
        return $this->contentFrom;
    }

    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentRelationInterface::setContentFrom()
     */
    public function setContentFrom( BaseContent $content )
    {
       $this->contentFrom = $content;
    }

    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentRelationInterface::getContentTo()
     */
    public function getContentTo()
    {
        return $this->contentTo;
    }

    /**
     * (non-PHPdoc)
     * @see ezp\Content\Persistence\API.ContentRelationInterface::setContentTo()
     */
    public function setContentTo( BaseContent $content )
    {
        $this->contentTo = $content;
    }
}