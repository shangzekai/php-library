<?php
/**
 * 读写日志工具类
 *
 * @author      chiak<Chiak@yeahmobi.com>
 * @package     library/sys
 * @since       Version 1.0 @2014-05-29
 * @copyright   Copyright (c) 2014, YeahMobi, Inc.
 */
class Sys_Log {
	private static $logpath = YPP_APP_LOG;

	/**
	 * 写入日志
	 *
	 * @param string $strFileName
	 * @param string $strType
	 * @param string $strMSG
	 * @param string $strExtra
	 * @param string $line
	 */
	public static function out($strFileName = "", $strType = "I", $strMSG = "", $strExtra = "", $line = "") {
		if ($strType == "")
			$strType = "I";

        if (!is_writable(self::$logpath))
        {
            @chmod(self::$logpath, 0777);
        }
        $logfile = rtrim(self::$logpath, '/') . '/' . $strFileName . '_' . date("ymd") . '.log';
        if (file_exists($logfile) && !is_writable($logfile))
        {
            @chmod($logfile, 0644);
        }
        $handle = @fopen($logfile, "a+");
        if ($handle)
        {
            // 判断是否CLI
            if (isset($_SERVER['SHELL']) && !isset($_SERVER['HTTP_HOST']))
            {
                $arg = "";
                if ($_SERVER['argc'] > 0)
                {
                    $arg = " ARGV:" . json_encode($_SERVER['argv']);
                }
                $strContent = "[" . date("Y-m-d H:i:s") . "] [" . strtoupper($strType) . "] [CLI] MSG:[" . $strMSG . "]" . $strExtra . " Location:" . $_SERVER["SCRIPT_FILENAME"] . $arg . ($line ? " Line:" . $line : "") . "\n";
            }
            else
                $strContent = "[" . date("Y-m-d H:i:s") . "] [" . strtoupper($strType) . "] [" . Sys_Tools::getRemoteAddr() . "] MSG:[" . $strMSG . "]" . $strExtra . " Location:" . $_SERVER["SCRIPT_FILENAME"] . ($line ? " Line:" . $line : "") . " QUERY_STRING:" . $_SERVER["QUERY_STRING"] . " HTTP_REFERER:" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "") . " User-Agent:" . $_SERVER["HTTP_USER_AGENT"] . "\n";
            if (!fwrite($handle, $strContent))
            {
                @fclose($handle);
                die("Write permission deny");
            }
            @fclose($handle);
        }
	}

	/**
	 * 将$strMSG写入$strFileName文件，覆盖原来内容
	 *
	 * @param $strFileName
	 * @param $strMSG
	 */
	public static function simplewrite($strFileName, $strMSG) {
        if (!is_writable(self::$logpath))
        {
            @chmod(self::$logpath, 0777);
        }
        $logfile = rtrim(self::$logpath, '/') . '/' . $strFileName . '.log';
        if (file_exists($logfile) && !is_writable($logfile))
        {
            @chmod($logfile, 0644);
        }
        $handle = @fopen($logfile, "w");
        if ($handle)
        {
            $strContent = $strMSG . "\n";
            if (!fwrite($handle, $strContent))
            {
                @fclose($handle);
                die("Write permission deny");
            }
            @fclose($handle);
        }
	}

	/**
	 * 写入文件，追加方式
	 *
	 * @param $strFileName
	 * @param $strMSG
	 */
	public static function simpleappend($strFileName, $strMSG) {
        if (!is_writable(self::$logpath))
        {
            @chmod(self::$logpath, 0777);
        }
        $logfile = rtrim(self::$logpath, '/') . '/' . $strFileName . '.log';
        if (file_exists($logfile) && !is_writable($logfile))
        {
            @chmod($logfile, 0644);
        }
        $handle = @fopen($logfile, "a");
        if ($handle)
        {
            $strContent = $strMSG . "\n";
            if (!fwrite($handle, $strContent))
            {
                @fclose($handle);
                die("Write permission deny");
            }
            @fclose($handle);
        }
	}

	/**
	 * 读文件内容
	 *
	 * @param $strFileName
	 *
	 * @return bool|string
	 */
	public static function simpleread($strFileName) {
		$logfile = trim(self::$logpath, '/') . '/' . $strFileName . '.log';
		if (file_exists($logfile) && is_readable($logfile))
		{
			$strContent = '';
			$handler = @fopen($logfile, 'r');
			if ($handler)
			{
				while (!feof($handler))
				{
					$strContent .= fgets($handler);
				}
				@fclose($handler);
			}

			return $strContent;
		}

		return false;
	}
}

?>
