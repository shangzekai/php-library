<?php
/**
 * Custom Session Handle Process
 *
 * Comply Access From The Database
 *
 * @author      Chiak<chiaki.sun@yeahmobi.com>
 * @package     library/sys
 * @since       Version 1.0 @2014-05-29
 */
class Sys_Session {

    private $dao;
    private $table;
    private $lifeTime;
    private static $instance;

    public function __construct()
    {
        // setting session handler callback function
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
        // get session max lifetime in config
        $this->lifeTime = ini_get('session.gc_maxlifetime');
        // get database dao object
        $this->dao = new Ym_Dao('log');

        $this->table  = Sys_Database::getTable('system_session');
    }

    /**
     * Single Mode, Return This Object
     * @return Sys_Session
     */
    public static function getInstance()
    {
        if(self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Open Session Interface
     * @param $savePath
     * @param $sessionName
     * @return bool
     */
    public function open($savePath, $sessionName)
    {
        if(!$this->dao) {
            return false;
        }
        return true;
    }

    /**
     * Close Session Interface
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Read Session
     * @param $id
     * @return mixed
     */
    public function read($id)
    {
        $sql = sprintf("SELECT `value` FROM `%s` WHERE `id` = '%s'", $this->table, $id);
        $session = $this->dao->queryRow($sql, true);
        return $session['value'];
    }

    /**
     * Write Session
     * @param $id
     * @param $value
     * @return bool
     */
    public function write($id, $value)
    {
        $expire = time() + $this->lifeTime;
        $sql = sprintf("REPLACE INTO `%s` VALUES('%s', '%s', '%d')", $this->table, $id, $value, $expire);
        return $this->dao->query($sql);
    }

    /**
     * Destroy Session
     * @param $id
     * @return bool
     */
    public function destroy($id)
    {
        $where = sprintf("`id ` = '%s'", $id);
        return $this->dao->delete($this->table, $where);
    }

    /**
     * Destroy Expired Session
     * @param $maxLifeTime
     * @return bool
     */
    public function gc($maxLifeTime)
    {
        $nowTime = time();
        $where = sprintf("`expire` < '%s'", $nowTime);
        return $this->dao->delete($this->table, $where);
    }


}