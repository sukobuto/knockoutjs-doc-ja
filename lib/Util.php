<?php
/**
 * ユーティリティクラス・関数
 * @author Kenta Suzuki (sukobuto.com)
 * @copyright Gadgetwear.Co.,ltd
 */

class Util {

	const MESSAGE_TYPE_NOTICE = 'notice';
	const MESSAGE_TYPE_ERROR = 'error';
	const MESSAGE_TYPE_ALERT = 'alert';
	private static $seq = 0;
	private static $stopwatches = array();


	/// Utitlty methods ///

	/**
	 * Get the value either of $_GET, $_POST, and $_COOKIE with trim.
	 * @param string $key Key of params;
	 * @return string trimmed value.
	 */
	public static function g($key) {
    	global $_GET, $_POST, $_COOKIE;

    	$param = false;

	    if (isset($_COOKIE[$key])) $param = $_COOKIE[$key];
	    if (isset($_POST[$key])) $param = $_POST[$key];
	    if (isset($_GET[$key])) $param = $_GET[$key];
	    if (is_array($param) && count($param) > 0) {
	    	$new_param = array();
	    	foreach ($param as $val) {
	    		$new_param[] = stripslashes($val);
	    	}
	    	$param = $new_param;
	    } else if ($param) {
	    	$param = stripslashes($param);
	    }
	    return $param;
	}

	/**
	 * Get the value either of $_GET, $_POST, and $_COOKIE with trim.
	 * @param string $key Key of params;
	 * @return string trimmed value.
	 */
	public static function gt($key) {
	    $val = self::g($key);
	    if ($val === false) return false;
	    return trim($val);
	}

	/**
	 * Get the value either of $_GET, $_POST, and $_COOKIE with trim.<br>
	 * When missed the parameter, response with 400 error, and exit.
	 * @param string $key Key of params;
	 * @return string trimmed value.
	 */
	public static function gr($key) {
		if (is_null($value = self::g($key))) {
			self::terminateWith400($key);
		}
		return $value;
	}

	/**
	 * Get random string. (MD5 hashcode)
	 * @param string $salt (opt) Use a salt string to hash.
	 * @return string Random string
	 */
	public static function getrand($salt = '') {
	    $fd = fopen("/dev/urandom","r");
	    $data = fread($fd,16);
	    fclose($fd);
	    return md5($data . $salt);
	}

	/**
	 * Output message to syslog
	 * @param integer $loglevel
	 * @param string $message
	 */
	public static function log($loglevel, $phpfilepath, $message) {
		$phpfilepath = basename($phpfilepath);
		syslog($loglevel, "[" . Def::SYSNAME . " ${phpfilepath}] ${message}");
	}

	public static function debugout($script_path, $script_line, $tag, $message, $file_prefix = "") {
		if (!Def::DEBUG_ENABLE) return;
		$script_name = $file_prefix . self::basename($script_path);
		$path = Def::DIR_DEBUG . DIRECTORY_SEPARATOR . $script_name . '.log';
		$datetime = date('Y/m/d H:i:s');
		if (is_array($message)) $message = self::objectToString($message);
		if ($fp = @fopen($path, "a")) {
			fwrite($fp, "\n[${datetime} on line ${script_line} tag:${tag}]\n");
			fwrite($fp, $message . "\n");
			fclose($fp);
		}
		return $message;
	}

	public static function basename($filepath) {
		$pos = strrpos($filepath, '/');
		if ($pos === false) return $filepath;
		return substr($filepath, $pos + 1);
	}

	public static function getExtension($filepath) {
		$filename = self::basename($filepath);
		$pos = strrpos($filename, '.');
		if ($pos === false) return '';
		return strtolower(substr($filename, $pos + 1));
	}

	public static function sessionSequence() {
		return self::$seq++;
	}

	/**
	 * Get object information as a string.
	 * @param mixed $obj
	 */
	public static function objectToString($obj) {
		ob_start();
		var_dump($obj);
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}

	/**
	 * Response to client with 'HTTP Status 400 Bad Request',
	 * and exit PHP.
	 * @param string $missing_param Parameter name for logging.
	 */
	public static function terminateWith400($missing_param = false) {
		header('HTTP', true, 400);
		if ($missing_param)
			self::log(LOG_WARNING, __FILE__, "missing a required param '${missing_param}'.");
		exit;
	}

	/**
	 * Response to client with 'HTTP Status 400 Bad Request',
	 * and exit PHP.
	 */
	public static function terminateWith404() {
		header('HTTP', true, 404);
		exit;
	}

	/**
	 * Response to client
	 * and exit PHP.
	 * @param integer $status_code (opt) HTTP status code. Default : 200 OK
	 * @param string $missing_param (opt) Parameter name for logging.
	 */
	public static function terminate($status_code = 200, $missing_param = false) {
		header('HTTP', true, $status_code);
		if ($missing_param)
			self::log(LOG_WARNING, __FILE__, "missing a required param '${missing_param}'.");
		exit;
	}

	public static function transfar($url, $exit = false) {
		header('Location: ' . $url);
		if ($exit) exit;
	}

	public static function addOneTimeMessage($message, $type = self::MESSAGE_TYPE_NOTICE) {
		@session_start();
		$_SESSION['one-time-messages'][] = array('message'=>$message, 'type'=>$type);
		setcookie('oneTimeMessage', $type . ':' . $message);
	}

	public static function getOneTimeMessages() {
		@session_start();
		if (isset($_SESSION['one-time-messages'])) {
			return $_SESSION['one-time-messages'];
		}
		return null;
	}

	public static function writeAsJson($var, $escape = false) {
		$json = json_encode($var);
		if ($escape) echo addslashes($json);
		else echo $json;
	}

	public static function append($filepath, $text) {
		if ($fp = @fopen($filepath, "a")) {
			flock($fp, LOCK_EX);
			fwrite($fp, $text);
			flock($fp, LOCK_UN);
			fclose($fp);
		}
	}

	public static function createOdkey() {
		return 'od_' . substr(sprintf("%09x", time()), 4) . substr(self::getrand(), 0, 4);
	}

	public static function edd($eddf_path, $data, $tag = false) {
		$edd
			= "[" . date('Y/m/d H:i:s') . "]" . ($tag ? "[${tag}]" : "" ) . "\n"
			. (is_array($data) ? self::objectToString($data) : $data)
			. "\n\n";
		self::append($eddf_path, $edd);
	}

	public static function watch_start($id) {
		self::$stopwatches[$id] = microtime(true);
	}

	public static function watch_split($id) {
		return microtime(true) - self::$stopwatches[$id];
	}

	/**
	 * 文字列の最大文字数を制限し、超過部分を置き換える
	 * @param string $str 対象文字列
	 * @param integer $len 最大文字数
	 * @param string $tailstr 置き換え文字列（デフォルト： '...'）
	 * @return string 処理済み文字列
	 */
	public static function str_maxlength($str, $len, $tailstr = '...') {
		if (mb_strlen($str, Def::ENCODING) > $len) {
			return mb_substr($str, 0, $len, Def::ENCODING) . $tailstr;
		} else {
			return $str;
		}
	}

	/**
	* 前方一致
	* $haystackが$needleから始まるか否かを判定する。
	* @param string $haystack
	* @param string $needle
	* @return TRUE = needleで始まる / FALSE = needleで始まらない
	*/
	public static function startsWith($haystack, $needle){
		return strpos($haystack, $needle, 0) === 0;
	}
	
	/**
	 * 後方一致
	 * $haystackが$needleで終わるか否かを判定する。
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean TRUE = needleで終わる / FALSE = needleで終わらない
	 */
	public static function endsWith($haystack, $needle) {
	    $length = strlen($needle);
	    if ($length == 0) {
	        return true;
	    }
	
	    return (substr($haystack, -$length) === $needle);
	}
}

function _p($var, $keys = false) {
	if (is_string($keys)) $keys = array($keys);
	else if (!is_array($keys) or count($keys) == 0) $keys = false;
	if (is_array($var)) {
		if (!$keys) {
			var_dump($var);
			return;
		}
		if (!isset($var[$keys[0]])) return;
		if (count($keys) == 1) echo $var[$keys[0]];
		else _p($var[$keys[0]], array_slice($keys, 1));
	} else {
		echo $var;
	}
}

class ArgumentException extends Exception {
	public function __construct($func, $argName, $type, $message = false) {
		$this->message = "${func} の引数 \$${argName} として渡された値が不正です。";
		if ($message) $this->message .= $message;
		else $this->message .= "値は ${type} である必要があります。";
	}
}

class InputErrors {
	private $inputErrors = array();

	public function __set($fieldName, $message) {
		$this->set($fieldName, $message);
	}

	public function __get($fieldName) {
		if (array_key_exists($fieldName, $this->inputErrors)) {
			return $this->inputErrors[$fieldName];
		} else return '';
	}

	public function set($fieldName, $message) {
		$this->inputErrors[$fieldName] = $message;
	}

	public function isError($fieldName) {
		return array_key_exists($fieldName, $this->inputErrors);
	}

	public function errorOccured() {
		return count($this->inputErrors) > 0;
	}

	public function printAll($delim = '<br />') {
		if (count($this->inputErrors) == 0) return;
		echo implode($delim, $this->inputErrors);
	}
}

/*
 * マルチバイト対応 str_replace()
 *
 * Release 3 update 1
 *
 * Copyright (C) 2006,2007,2011,2012 by HiNa <hina@bouhime.com>. All rights reserved.
 *
 * LICENSE
 *
 * This source file is subject to the 2-clause BSD License(Simplified
 * BSD License) that is bundled with this package in the file LICENSE.
 * The license is also available at this URL:
 * https://github.com/fetus-hina/mb_str_replace/blob/master/LICENSE
 *
 * http://fetus.k-hsu.net/document/programming/php/mb_str_replace.html
 */
if(!function_exists('mb_str_replace')) {
    /**
     * マルチバイト対応 str_replace()
     *
     * @param   mixed   $search     検索文字列（またはその配列）
     * @param   mixed   $replace    置換文字列（またはその配列）
     * @param   mixed   $subject    対象文字列（またはその配列）
     * @param   string  $encoding   文字列のエンコーディング(省略: 内部エンコーディング)
     *
     * @return  mixed   subject 内の search を replace で置き換えた文字列
     *
     * この関数の $search, $replace, $subject は配列に対応していますが、
     * $search, $replace が配列の場合の挙動が PHP 標準の str_replace() と異なります。
     */
    function mb_str_replace($search, $replace, $subject, $encoding = 'auto') {
        if(!is_array($search)) {
            $search = array($search);
        }
        if(!is_array($replace)) {
            $replace = array($replace);
        }
        if(strtolower($encoding) === 'auto') {
            $encoding = mb_internal_encoding();
        }

        // $subject が複数ならば各要素に繰り返し適用する
        if(is_array($subject) || $subject instanceof Traversable) {
            $result = array();
            foreach($subject as $key => $val) {
                $result[$key] = mb_str_replace($search, $replace, $val, $encoding);
            }
            return $result;
        }

        $currentpos = 0;    // 現在の検索開始位置
        while(true) {
            // $currentpos 以降で $search のいずれかが現れる位置を検索する
            $index = -1;    // 見つけた文字列（最も前にあるもの）の $search の index
            $minpos = -1;   // 見つけた文字列（最も前にあるもの）の位置
            foreach($search as $key => $find) {
                if($find == '') {
                    continue;
                }
                $findpos = mb_strpos($subject, $find, $currentpos, $encoding);
                if($findpos !== false) {
                    if($minpos < 0 || $findpos < $minpos) {
                        $minpos = $findpos;
                        $index = $key;
                    }
                }
            }

            // $search のいずれも見つからなければ終了
            if($minpos < 0) {
                break;
            }

            // 置換実行
            $r = array_key_exists($index, $replace) ? $replace[$index] : '';
            $subject =
                mb_substr($subject, 0, $minpos, $encoding) .    // 置換開始位置より前
                $r .                                            // 置換後文字列
                mb_substr(                                      // 置換終了位置より後ろ
                    $subject,
                    $minpos + mb_strlen($search[$index], $encoding),
                    mb_strlen($subject, $encoding),
                    $encoding);

            // 「現在位置」を $r の直後に設定
            $currentpos = $minpos + mb_strlen($r, $encoding);
        }
        return $subject;
    }
}