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
$encoded = $argv[2];
$block = new BlockCode($salt);

$decoded = $block->decode($encoded);
echo $decoded;