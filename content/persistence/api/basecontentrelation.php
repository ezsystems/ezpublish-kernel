<?php
namespace ezp\Content\Persistence\API;

abstract class BaseContentRelation
{
    const RELATION_TYPE_COMMON = 1,
          RELATION_TYPE_XML_LINK = 2,
          RELATION_TYPE_XML_EMBED = 3;

    protected $relationType;

    /**
     * Content the relation originates from
     * @var BaseContent
     */
    protected $contentFrom;

    /**
     * Content the relation points to
     * @var BaseContent
     */
    protected $contentTo;
}
?>
