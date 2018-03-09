<?php
/**
 * 业务监控系统用户会话
 *
 * 管理用户会话和相关工具
 *
 * @author      chiak<Chiak@yeahmobi.com>
 * @package     library/user
 * @since       Version 1.0 @2014-05-28
 * @copyright   Copyright (c) 2014, YeahMobi, Inc.
 */
class User_Session {

    // 用户登陆密码的混淆字符，除非系统废除等特殊原因否则常量值永远不变
    const LOGIN_SALT = 'cf45eef37f17184eca18fda007067fa8';
    // 用户登录会话的混淆字符
    const SESSION_SALT = '96d9645d58abe1e7ce29987c6c1453d4';
    // 必须登陆
    const LOGIN_MUST = '1';
    // 无需登录
    const LOGIN_WITHOUT = '2';

    /**
     * 自定义可逆加密
     * @param $data 加密数据
     * @return string
     */
    public function encrypt($data)
    {
        $key	=	self::SESSION_SALT;
        $x		=	0;
        $len	=	strlen($data);
        $l		=	strlen($key);
        $char = '';
        $str = '';
        for ($i = 0; $i < $len; $i++)
        {
            if ($x == $l)
            {
                $x = 0;
            }
            $char .= $key{$x};
            $x++;
        }
        for ($i = 0; $i < $len; $i++)
        {
            $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
        }
        return base64_encode($str);
    }

    /**
     * 自定义可逆解密
     * @param $data 解密数据
     * @return string
     */
    public function decrypt($data)
    {
        $key = self::SESSION_SALT;
        $x = 0;
        $data = base64_decode($data);
        $len = strlen($data);
        $l = strlen($key);
        $char = '';
        $str = '';
        for ($i = 0; $i < $len; $i++)
        {
            if ($x == $l)
            {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        for ($i = 0; $i < $len; $i++)
        {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
            {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            }
            else
            {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return $str;
    }

    /**
     * 检测登陆状态
     * @return bool
     */
    public static function detectLogin()
    {
        $loginCookie = self::getLoginCookie();
        $loginSession = self::getLoginSession();

        if(empty($loginCookie) || $loginCookie != $loginSession) {
            // 登陆异常，删除登陆Cookie，更新登陆会话状态为异常
            self::delLoginCookie();
            self::delLoginSession();
            return false;
        }
        return true;
    }

    /**
     * 设置登录Cookie
     * @param $loginInfo
     */
    public static function setLoginCookie($loginInfo)
    {
        // 加密用户信息字符串
        $info = self::encrypt(serialize($loginInfo));
        setcookie("LOGIN_INFO", $info, time() + 3600 * 24, '/');
    }

    /**
     * 获取登陆Cookie
     * @return mixed
     */
    public static function getLoginCookie(){
        $loginInfo = null;
        if(isset($_COOKIE['LOGIN_INFO'])) {
            $loginInfo = unserialize(self::decrypt($_COOKIE['LOGIN_INFO']));
        }
        return $loginInfo;
    }

    /**
     * 删除登陆Cookie
     */
    public static function delLoginCookie()
    {
        setcookie('LOGIN_INFO', '', time() - 3600, '/');
    }

    /**
     * 设置登录IDCookie
     * @param $uid
     */
    public static function setUserIdCookie($uid) {
        // 加密用户信息字符串
        setcookie("USER_ID", $uid, time() + 3600 * 24, '/');
    }

    /**
     * 获取登陆Cookie
     * @return mixed
     */
    public static function getUserIdCookie(){
        $returns = null;
        if(isset($_COOKIE['USER_ID'])) {
            $returns = $_COOKIE['USER_ID'];
        }
        return $returns;
    }

    /**
     * 设置登录IDCookie
     * @param $business
     */
    public static function setBusiness($business) {
        // 加密用户信息字符串
        setcookie("USER_BUSINESS", $business, time() + 3600 * 24, '/');
    }

    /**
     * 获取登陆Cookie
     * @return mixed
     */
    public static function getBusiness(){
        $returns = null;
        if(isset($_COOKIE['USER_BUSINESS'])) {
            $returns = $_COOKIE['USER_BUSINESS'];
        }
        return $returns;
    }

    /**
     * 删除登陆Cookie
     */
    public static function delUserIdCookie()
    {
        setcookie('USER_ID', '', time() - 3600, '/');
    }

    /**
     * 设置登录会话
     * @param $loginInfo
     */
    public static function setLoginSession($loginInfo) {
        $loginInfo = serialize($loginInfo);
        Yaf_Session::getInstance()->set('LOGIN_INFO', $loginInfo);
    }

    /**
     * 获取登陆会话
     * @return mixed
     */
    public static function getLoginSession(){
        $loginSession = unserialize(Yaf_Session::getInstance()->get('LOGIN_INFO'));
        return $loginSession;
    }

    /**
     * 删除登陆会话
     */
    public static function delLoginSession() {
        Yaf_Session::getInstance()->del('LOGIN_INFO');
    }

    /**
     * 设置返回页面
     * @param $backPage
     */
    public static function setBackPage($backPage)
    {
        Yaf_Session::getInstance()->set('BACK_PAGE', $backPage);
    }

    /**
     * 获取返回页面
     * @return mixed
     */
    public static function getBackPage()
    {
        return Yaf_Session::getInstance()->get('BACK_PAGE');
    }

    /**
     * 设置SESSION通知消息
     * @param $notice
     */
    public static function setNotice($notice)
    {
        $notice = serialize($notice);
        Yaf_Session::getInstance()->set('NOTICE', $notice);
    }

    /**
     * 从SESSION获取通知消息
     * @return mixed
     */
    public static function getNotice()
    {
        $notice = unserialize(Yaf_Session::getInstance()->get('NOTICE'));
        return $notice;
    }

    /**
     * 从SESSION删除通知消息
     */
    public static function delNotice()
    {
        Yaf_Session::getInstance()->del('NOTICE');
    }

}
