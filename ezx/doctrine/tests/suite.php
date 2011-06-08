<?php

namespace ezx\doctrine\tests;
class Suite extends \PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'ezp-next Doctrine Test Suite' );
        //$this->addTestSuite( __NAMESPACE__ . '\\LocationTest'  );//PHPUnit_Framework_TestCase
    }

    /**
     * @return ezpTestSuite
     */
    public static function suite()
    {
        return new self();
    }
}
