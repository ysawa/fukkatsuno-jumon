<?php
/**
 * Created by PhpStorm.
 * User: ysawa
 * Date: 11/1/16
 * Time: 00:50
 */

require __DIR__ . '/../vendor/autoload.php';
use FukkatsunoJumon\ParityCheckCode;
use FukkatsunoJumon\BlockCode;

$salt = $argv[1];
$plaintext = $argv[2];
$block = new BlockCode($salt);

var_dump('PLAINTEXT: ' . $plaintext);
$encoded = $block->encode($plaintext);
var_dump('ENCODED: ' . $encoded);
$decoded = $block->decode($encoded);
var_dump('DECODED: ' . $decoded);