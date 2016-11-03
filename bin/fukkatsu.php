<?php
/**
 * Created by PhpStorm.
 * User: ysawa
 * Date: 11/1/16
 * Time: 00:50
 */

require __DIR__ . '/../vendor/autoload.php';
use FukkatsunoJumon\ParityCheckCode;

$code = new ParityCheckCode(0);

for ($i = 0; $i < 8; $i++) {
	$generated = $code->generate($i);
	$encoded = $code->convertToHumanCode($generated);
	var_dump($i, $generated, $encoded);
}
