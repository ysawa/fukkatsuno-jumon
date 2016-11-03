<?php
/**
 * Created by PhpStorm.
 * User: ysawa
 * Date: 11/3/16
 * Time: 21:19
 */

namespace FukkatsunoJumon;


class BlockCode
{
	private $_salt;

	public function __construct($salt) {
		$this->_salt = $salt;
	}

	/**
	 * @param string $decoded
	 * @return null|string
	 */
	public function decode($decoded) {
		$decoded = '';
		return $decoded;
	}

	/**
	 * @param string $plaintext
	 * @return string
	 */
	public function encode($plaintext) {
		$encoded = '';
		return $encoded;
	}
}