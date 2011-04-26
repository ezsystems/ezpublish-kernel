<?php
namespace ezp;

interface Repository
{
    /**
     * Creates a new content and returns it
     * @return ezpContent
     */
    public function createContent();
}
?>