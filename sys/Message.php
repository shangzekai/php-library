<?php
class Sys_Message
{
    /*private static $smsApi  = 'http://10.2.5.20:8080/notify/api/sms';
    private static $mailApi = 'http://10.2.5.20:8080/notify/api/email';*/
	
	private static $smsApi  = 'http://ips.ymtech.info/notify/api/sms';
	//private static $smsApi  = 'http://10.2.5.20:8080/notify/api/sms';
    private static $mailApi = 'http://ips.ymtech.info/notify/api/email';
    
    private static $appkey = 'c8s2j8#c';

    /**
     * 发送消息
     *
     * @param string $type
     * @param $to
     * @param $subject
     * @param $body
     */
    public static function send($type, $to, $subject, $body, $ip, $app='monitor') {

        switch ($type) {
            case 'mail':
            	Ym_Logger::debug('------------------sendMail to:'.$to);
                $result = self::sendMail($to, $subject, $body);
                break;
            case 'sms':
            	Ym_Logger::debug('------------------sendSms to:'.$to);
                $result = self::sendSms($to, $ip, $subject, $app);
                break;
        }

        return $result;
    }

    /**
     * 发送短信
     *
     * @param $to
     * @param $ip
     * @param $body
     * @return mixed
     */
    private static function sendSms($to, $ip, $body, $app='monitor') {

        $returns = null;

        $data = array(
            'to'   => $to,
            'ip'   => $ip,
            'text' => $body,
        	'_app'  => 'monitor',
        	'_time' => time(),
        	'_sign' => substr(md5('monitor'.self::$appkey.time()), 0, 8)
        );
        
        $returns = self::post(self::$smsApi, $data);
        return $returns;
    }

    /**
     * 发送邮件
     *
     * @param $to
     * @param $subject
     * @param $body
     * @return mixed
     */
    private static function sendMail($to, $subject, $body) {

        $returns = null;

        $data = array(
            'to'      => $to,
            'subject' => $subject,
            'body'    => $body
        );

        $returns = self::post(self::$mailApi, $data);

        return $returns;
    }

    /**
     * Post请求
     *
     * @param $url
     * @param $data
     * @return mixed|null
     */
    private static function post($url, $data) {

        $returns = null;
        $p = '';

        if (is_array($data)) {

            /*$dataStr = '';

            foreach ($data AS $key => $val) {

                $dataStr .= $key . '=' . $val . '&';
            }

            $data = substr($dataStr, 0, strlen($dataStr) - 1);*/
        	
        	$p = http_build_query($data);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $p);

        $returns = curl_exec($ch);

        curl_close($ch);

        return $returns;
    }
} 