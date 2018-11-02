<?php

class php_coinbase_minersTest extends PHPUnit_Framework_TestCase {

	public function testPoolFileFormat() {
        $poolarray = include('pools.php');
        $this->assertEquals('array',gettype($poolarray));
    }

	public function testPoolFileLastEntry() {
        $poolarray = include('pools.php');
        $entry = $poolarray[sizeof($poolarray)-1];
        $this->assertEquals('ltc.top',$entry['name']);
        $this->assertEquals('http://ltc.top/',$entry['url']);
        $this->assertEquals('LTC.TOP',$entry['cbstrings'][0]);
    }

}
