<?php
class Sys_Common {

    public static function showError($message, $file) {
        $logConfig = array(
            'logPath' => '/tmp/',
            'logFile' => $file,
            'handler' => 'file',
            'ifCacheHandler' => TRUE);

        Ym_Logger::init($logConfig);
        Ym_Logger::info($message);
        exit($message);
    }

    public static function command($url='', $asJob=TRUE, $return=TRUE) {

        $job = "";
        $out = ">/dev/null 2>&1";
        $phpcmd = "/bin/php";
        $yafClient = Ym_Config::getAppItem("application:application.client");
        $requestUri = "request_uri=".$url;

        if ($asJob) $job = "&";
        if ($return) $out = "";

        $cmd = trim($phpcmd." ".$yafClient." '".$requestUri."' ".$out." ".$job);

        $result = exec($cmd);

        return $result;
    }

    public static function convertSize($size) {
        $return = null;

        if ($size > pow(1024, 3)) {
            $return = number_format($size / pow(1024, 3), 3) . " GB";
        } else if ($size > pow(1024, 2)) {
            $return = number_format($size / pow(1024, 2), 3) . " MB";
        } else {
            $return = number_format($size / 1024, 3) . " KB";
        }

        return $return;
    }

    public static function output($flag = true, $msg = '', $data = array()) {
        if ($flag) {
            $out = array('flag' => 'success', 'msg' => $msg, 'data' => $data);
        } else {
            $out = array('flag' => 'error',   'msg' => $msg, 'data' => $data);
            Ym_Logger::error($msg);
        }

        Ym_CommonTool::output(NULL, $out, 'json');
    }

} 