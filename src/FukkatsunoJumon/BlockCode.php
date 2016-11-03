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
	private $_code;
	private $_salt;

	/**
	 * BlockCode constructor.
	 * @param string $salt
	 */
	public function __construct($salt) {
		$this->_code = new ParityCheckCode();
		$this->_salt = $this->_code->convertHumanCodeToEncoded($salt);
	}

	/**
	 * @param string $decoded
	 * @return null|string
	 */
	public function decode($decoded) {
		$length = floor(mb_strlen($decoded) / 2);
		$plaintext = '';
		$salt = $this->_salt;
		for ($i = 0; $i < $length; $i++) {
			$string = mb_substr($decoded, $i * 2, 2);
			$encoded = $this->_code->convertHumanCodeToEncoded($string);
			if ($encoded === false) {
				return null;
			}
			$nextSalt = $encoded;
			$encoded = $encoded ^ $salt;
			$salt = $nextSalt;
			$generated = $this->_code->decode($encoded);
			if ($generated === false) {
				return null;
			}
			$plaintext .= chr($generated);
		}
		return $plaintext;
	}

	/**
	 * @param string $plaintext
	 * @return string
	 */
	public function encode($plaintext) {
		$length = strlen($plaintext);
		$encoded = '';
		$salt = $this->_salt;
		for ($i = 0; $i < $length; $i++) {
			$string = ord($plaintext[$i]);
			$generated = $this->_code->generate($string);
			$salt = $generated = $generated ^ $salt;
			$encoded .= $this->_code->convertToHumanCode($generated);
		}
		return $encoded;
	}
}