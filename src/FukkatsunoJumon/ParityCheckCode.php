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

	public function __construct() {
		$toInt = function ($string) {
			return bindec($string);
		};
		$this->_checkCodeArray = array_map($toInt, preg_split("/\r\n|\r|\n/", self::CHECK_CODE_STRING));
		$this->_generatorCodeArray = array_map($toInt, preg_split("/\r\n|\r|\n/", self::GENERATOR_CODE_STRING));
	}

	/**
	 * @param int $vector0
	 * @param int $vector1
	 * @return int (0 or 1)
	 */
	protected static function dotP($vector0, $vector1) {
		$vector = $vector0 & $vector1;
		$numbers = str_split(decbin($vector));
		return (array_sum($numbers) % 2);
	}

	/**
	 * @param $humanCode
	 * @return bool|int
	 */
	public function convertHumanCodeToEncoded($humanCode) {
		if (empty($humanCode)) {
			return false;
		}

		$splittedHumanCode = [];
		$length = mb_strlen($humanCode);
		for ($i = 0; $i < $length; $i++) {
			$subString = mb_substr($humanCode, $i, 1);
			if (in_array($subString, self::$HUMAN_CODE_MAP)) {
				$splittedHumanCode[] = $subString;
			}
		}

		if (count($splittedHumanCode) !== self::GENERATED_CODE_LENGTH / self::HUMAN_CODE_LENGTH) {
			return false;
		}

		/**
		 * @param string $character
		 * @return integer
		 */
		$toInt = function ($character) {
			return array_search($character, self::$HUMAN_CODE_MAP);
		};

		$generated = 0;
		foreach (array_map($toInt, $splittedHumanCode) as $code) {
			$generated = ($generated << self::HUMAN_CODE_LENGTH) | $code;
		}

		return $generated;
	}

	/**
	 * @param int $generated
	 * @return string
	 */
	public function convertToHumanCode($generated)
	{
		$string = decbin($generated);

		$format = "%0" . self::GENERATED_CODE_LENGTH . 's';
		$generatedCode = sprintf($format, $string);

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

	/**
	 * @param string $humanCode
	 * @return bool|number
	 */
	public function decodeHumanCode($humanCode) {
		if (!$this->isValidHumanCode($humanCode)) {
			return false;
		}
		$code = $this->convertHumanCodeToEncoded($humanCode);
		return $this->decode($code);
	}

	/**
	 * @param number $message
	 * @return number
	 */
	public function generate($message) {
		$codeString = '';
		for ($i = 0; $i < self::GENERATED_CODE_LENGTH; $i++) {
			$codeString .= self::dotP($message, $this->_generatorCodeArray[$i]);
		}
		return bindec($codeString);
	}

	/**
	 * @param number $generated
	 * @return bool
	 */
	public function isValidGenerated($generated) {

		for ($i = 0; $i < self::CHECK_CODE_LENGTH; $i++) {
			$syndrome = self::dotP($generated, $this->_checkCodeArray[$i]);
			if ($syndrome !== 0) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param string $humanCode
	 * @return bool
	 */
	public function isValidHumanCode($humanCode) {
		$generated = $this->convertHumanCodeToEncoded($humanCode);
		if ($generated !== false) {
			return $this->isValidGenerated($generated);
		} else {
			return false;
		}
	}
}