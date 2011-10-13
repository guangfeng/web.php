<?php
require 'simpletest.class.php';

$t = new Simpletest;
$t->get('http://web.vmstack.tk/');
$t->assertEqual(json_encode(array('code'=>1)));
var_dump($t->runner);