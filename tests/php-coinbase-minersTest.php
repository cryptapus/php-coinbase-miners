<?php

class php_coinbase_minersTest extends \PHPUnit\Framework\TestCase {

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

	public function testFunction_num2sci() {
        $poolarray = include('utils.php');
        $this->assertEquals('10.000',num2sci(9.9999));
        $this->assertEquals('100.000',num2sci(99.9999));
        $this->assertEquals('9.200',num2sci(9.1999));
        $this->assertEquals('9.200K',num2sci(floatval('9.1999e3')));
        $this->assertEquals('9.200M',num2sci(floatval('9.1999e6')));
        $this->assertEquals('-9.200G',num2sci(floatval('-9.1999e9')));
        $this->assertEquals('9.200T',num2sci(floatval('9.1999e12')));
        $this->assertEquals('-9.200P',num2sci(floatval('-9.1999e15')));
        $this->assertEquals('9.200E',num2sci(floatval('9.1999e18')));
        $this->assertEquals('-9.200Z',num2sci(floatval('-9.1999e21')));
        $this->assertEquals('9.200Y',num2sci(floatval('9.1999e24')));
        $this->assertEquals('-9.999Y',num2sci(floatval('-9.9994e24')));
        $this->assertEquals('&gt;1000Y',num2sci(floatval('999999.9995e24')));
        $this->assertEquals('1.000',num2sci(0.9999999));
        $this->assertEquals('0.920',num2sci(0.91999));
        $this->assertEquals('19.990m',num2sci(0.01999));
        $this->assertEquals('1.990m',num2sci(0.00199));
        $this->assertEquals('9.200m',num2sci(floatval('9.1999e-3')));
        $this->assertEquals('9.200&micro;',num2sci(floatval('9.19999e-6')));
        $this->assertEquals('-9.200n',num2sci(floatval('-9.1999e-9')));
        $this->assertEquals('9.200p',num2sci(floatval('9.1999e-12')));
        $this->assertEquals('-9.200f',num2sci(floatval('-9.1999e-15')));
        $this->assertEquals('9.200a',num2sci(floatval('9.1999e-18')));
        $this->assertEquals('-9.200z',num2sci(floatval('-9.1999e-21')));
        $this->assertEquals('1.001y',num2sci(floatval('1.001e-24')));
        $this->assertEquals('&lt;1.000y',num2sci(floatval('0.9999e-24')));
        $this->assertEquals('0.135z',num2sci(floatval('135.1668498e-24')));
        $this->assertEquals('94.177P',num2sci(floatval('94.17666454e15')));
        $this->assertEquals('21.146M',num2sci(floatval('21.145967e6')));
    }

}
