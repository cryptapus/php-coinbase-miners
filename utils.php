<?php
/*
====================

The MIT License (MIT)

Copyright (c) 2018 cryptapus <info@cryptapus.org>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

====================
*/

function hex2str($hex) {
    $str = '';
    for($i=0;$i<strlen($hex);$i+=2) $str .= chr(hexdec(substr($hex,$i,2)));
    return $str;
}

function num2sci($num) {
    if ($num<0) {
        $ret = '-';
        $num = abs($num);
    } else {
        $ret = '';
    }
    if ($num>=floatval('1000e24')) {
        return '&gt;1000Y';
    }
    if ($num<floatval('1.0e-24')) {
        return '&lt;1.000y';
    }
    if ($num>=1) {
        $unim = array("","K","M","G","T","P","E","Z","Y");
        $c = 0;
        while ($num>=1000) {
            $c++;
            $num = $num/1000;
        }
        $ret = $ret.number_format($num,($c ? 3 : 3),".",",")."".$unim[$c];
    } else {
        $unim = array("","m","&micro;","n","p","f","a","z","y");
        $c = 0;
        while ($num<=0.1) {
            $c++;
            $num = $num*1000;
        }
        $ret = $ret.number_format($num,($c ? 3 : 3),".",",")."".$unim[$c];
    }
    return $ret;
}
