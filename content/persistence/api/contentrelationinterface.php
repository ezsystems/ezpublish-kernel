<?php
namespace ezp\Content\Persistence\API;

interface ContentRelationInterface extends BaseContentInterface
{
    /**
     * Returns content the relation originates from
     * @return BaseContent
     */
    public function getContentFrom();

    /**
     * Sets content the relation originates from, to $content
     * @param BaseContent $content
     */
    public function setContentFrom( BaseContent $content );

    /**
     * Returns the content the relation points to
     * @return BaseContent
     */
    public function getContentTo();

    /**
     * Sets the content the relation points to, to $content
     * @param BaseContent $content
     */
    public function setContentTo( BaseContent $content );

    /**
     * Returns the relation type as integer.
     * This integer relates to constants declared in BaseContentRelation
     * i.e. BaseContentRelation::RELATION_TYPE_COMMON or BaseContentRelation::RELATION_TYPE_XML_LINK
     * @return int
     */
    public function getRelationType();

    /**
     * Sets relation type
     * @param int $relationType One of the constants value set in BaseContentRelation, e.g. BaseContentRelation::RELATION_TYPE_COMMON
     */
    public function setRelationType( $relationType );
}