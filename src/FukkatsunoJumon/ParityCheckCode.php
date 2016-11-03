<?php
/**
 * Created by PhpStorm.
 * User: ysawa
 * Date: 11/1/16
 * Time: 00:49
 */

namespace FukkatsunoJumon;


class ParityCheckCode
{
	const CHECK_CODE_LENGTH = 4;
	const CHECK_CODE_STRING = <<<CODE
100010010101
010011001011
001001100101
000100110011
CODE;
	const GENERATED_CODE_LENGTH = 12;
	const GENERATOR_CODE_STRING = <<<CODE
10010101
11001011
01100101
00110011
10000000
01000000
00100000
00010000
00001000
00000100
00000010
00000001
CODE;

	const MESSAGE_CODE_LENGTH = 8;
	const HUMAN_CODE_LENGTH = 6;

	private static $HUMAN_CODE_MAP = [
		'あ','い','う','え','お','か','き','く','け','こ','さ','し','す','せ','そ',
		'た','ち','つ','て','と','な','に','ぬ','ね','の','は','ひ','ふ','へ','ほ',
		'ま','み','む','め','も','ら','り','る','れ','ろ','が','ぎ','ぐ','げ','ご',
		'ざ','じ','ず','ぜ','ぞ','ば','び','ぶ','べ','ぼ','ぱ','ぴ','ぷ','ぺ','ぽ',
		'や','ゆ','よ','わ',];

	private $_checkCodeArray;
	private $_generatorCodeArray;
	private $_humanCodeMap;
	private $_salt;

	public function __construct($salt) {
		$toInt = function ($string) {
			return bindec($string);
		};
		$this->_salt = $salt;
		$this->_checkCodeArray = array_map($toInt, preg_split("/\r\n|\r|\n/", self::CHECK_CODE_STRING));
		$this->_generatorCodeArray = array_map($toInt, preg_split("/\r\n|\r|\n/", self::GENERATOR_CODE_STRING));
	}

	/**
	 * @param int $vector0
	 * @param int $vector1
	 * @return int (0 or 1)
	 */
	protected static function innerProduct($vector0, $vector1) {
		$vector = $vector0 & $vector1;
		$numbers = str_split(decbin($vector));
		return (array_sum($numbers) % 2);
	}

	public function convertHumanCodeToEncoded($humanCode) {
		if (empty($humanCode)) {
			return false;
		}

		$splittedHumanCode = array_intersect(
			str_split($humanCode),
			$this->_humanCodeMap
		);
		if (count($splittedHumanCode) !== (self::GENERATED_CODE_LENGTH / 8)) {
			return false;
		}

		$toInt = function ($character) {
			return array_search($character, $this->_humanCodeMap);
		};

		$generated = 0;
		foreach (array_map($toInt, $splittedHumanCode) as $code) {
			$generated = ($generated << 5) | $code;
		}

		return $generated;
	}

	public function convertGeneratedToHumanCode($generated) {

		$toHumanCode = function ($string) {
			return $this->_humanCodeMap[bindec($string)];
		};
		$format = "%0" . self::GENERATED_CODE_LENGTH . 's';
		$generatedCode = sprintf($format, decbin($generated));
		$splitted = str_split($generatedCode, self::HUMAN_CODE_LENGTH);
		return implode('', array_map($toHumanCode, $splitted));
	}

	/**
	 * @param int $generated
	 * @return string
	 */
	public function convertToHumanCode($generated)
	{
		$string = decbin($generated);
		$length = strlen($string);

		if (($length % self::GENERATED_CODE_LENGTH) === 0) {
			$generatedCode = $string;
		} else {
			$nextLength = (floor($length / self::GENERATED_CODE_LENGTH) + 1) * self::GENERATED_CODE_LENGTH;
			$format = "%0" . $nextLength . 's';
			$generatedCode = sprintf($format, $string);
		}

		$splitted = str_split($generatedCode, self::HUMAN_CODE_LENGTH);
		return implode('', array_map(function ($string) {
			return self::$HUMAN_CODE_MAP[bindec($string)];
		}, $splitted));
	}

	public function decode($code) {
		$codeString = decbin($code);
		$messageString = substr($codeString, - self::MESSAGE_CODE_LENGTH);
		return bindec($messageString);
	}

	public function decodeHumanCode($humanCode) {
		if (!$this->isValidHumanCode($humanCode)) {
			return false;
		}
		$code = $this->convertHumanCodeToEncoded($humanCode);
		return $this->decode($code);
	}

	/**
	 * @param string $message
	 * @return string
	 */
	public function encode($message) {
		$code = '';
		return $code;
	}

	public function generate($message) {
		$codeString = '';
		for ($i = 0; $i < self::GENERATED_CODE_LENGTH; $i++) {
			$codeString .= self::innerProduct($message, $this->_generatorCodeArray[$i]);
		}
		return bindec($codeString);
	}

	public function isValidGenerated($generated) {

		for ($i = 0; $i < self::CHECK_CODE_LENGTH; $i++) {
			$syndrome = self::innerProduct($generated, $this->_checkCodeArray[$i]);
			if ($syndrome !== 0) {
				return false;
			}
		}

		return true;
	}

	public function isValidHumanCode($humanCode) {
		$generated = $this->convertHumanCodeToEncoded($humanCode);
		if ($generated) {
			return $this->isValidGenerated($generated);
		} else {
			return false;
		}
	}
}