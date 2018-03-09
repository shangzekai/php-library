<?php
/**
 * MongoDao
 *
 * @author zekai
 * @version 1.0.0
 * @package library/dao
 */
class Dao_Mongo {

    private $host;
    private $port;
    private $user;
    private $pass;
    private $dbName;
    private $persist;
    private $persistKey;
    private $querySafety;

    private $connectionString;
    private $connection;
    public $db;

    private $selects = array();
    private $wheres = array();
    private $sorts = array();

    private $limit = 999999;
    private $offset = 0;

    public function __construct($name)
    {
        $this->setConnectionString($name);
        $this->connect();
    }

    /**
     * 设置连接字符串
     */
    private function setConnectionString($name)
    {
        Ym_Config::init();
        $config = Ym_Config::getAppItem('monitor');

        $this->host         = trim($config['mongo'][$name]['host']);
        $this->port         = trim($config['mongo'][$name]['port']);
        $this->user         = trim($config['mongo'][$name]['user']);
        $this->pass         = trim($config['mongo'][$name]['pass']);
        $this->dbName       = trim($config['mongo'][$name]['dbname']);
        $this->querySafety  = trim($config['mongo'][$name]['query_safety']);

        $connectionString = "mongodb://";

        if(empty($this->host)) {
            Sys_Common::showError("The Host must be set to connect to MongoDB", 'MongoDB');
        }

        if(empty($this->dbName)) {
            Sys_Common::showError("The Database must be set to connect to MongoDB", 'MongoDB');
        }

        if(!empty($this->user) && !empty($this->pass)) {
            $connectionString .= "{$this->user}:{$this->pass}";
        }

        if(isset($this->port) && !empty($this->port)) {
            $connectionString .= "{$this->host}:{$this->port}";
        } else {
            $connectionString .= "{$this->host}";
        }

        $this->connectionString = trim($connectionString);
    }

    /**
     * 连接到MongoDB
     */
    public function connect()
    {
        $options = array();

        try {
            $this->connection = new MongoClient($this->connectionString, $options);
            $this->db = $this->connection->{$this->dbName};
            return $this;
        } catch (MongoConnectionException $e) {
            Sys_Common::showError("Unable to connect to MongoDB:{$e->getMessage()}", 'MongoDB');
        }
    }

    /**
     * 选择数据库
     *
     * @param string $dataBase
     * @return bool
     */
    public function switchDb($dataBase = '')
    {
        $this->dbName = $dataBase;
        try {
            $this->db = $this->connection->{$this->dbName};
            return TRUE;
        } catch (Exception $e) {
            Sys_Common::showError("Unable to connect to MongoDB:{$e->getMessage()}", 'MongoDB');
        }
    }

    /**
     * 选择查询字段
     *
     * @param array $includes
     * @param array $excludes
     * @return $this
     */
    public function select($includes = array(), $excludes = array())
    {
        if ( ! is_array($includes))
        {
            $includes = array();
        }

        if ( ! is_array($excludes))
        {
            $excludes = array();
        }

        if ( ! empty($includes))
        {
            foreach ($includes as $col)
            {
                $this->selects[$col] = 1;
            }
        }
        else
        {
            foreach ($excludes as $col)
            {
                $this->selects[$col] = 0;
            }
        }
        return $this;
    }

    /**
     * 设置where属性
     *
     * @param array $wheres
     * @return $this
     */
    public function where($wheres = array())
    {
        foreach ($wheres as $wh => $val)
        {
            $this->wheres[$wh] = $val;
        }
        return $this;
    }

    /**
     * 增加OR条件
     *
     * @param array $wheres
     * @return $this
     */
    public function orWhere($wheres = array())
    {
        if (count($wheres) > 0)
        {
            if ( ! isset($this->wheres['$or']) || ! is_array($this->wheres['$or']))
            {
                $this->wheres['$or'] = array();
            }

            foreach ($wheres as $wh => $val)
            {
                $this->wheres['$or'][] = array($wh=>$val);
            }
        }
        return $this;
    }

    /**
     * 增加IN条件
     *
     * @param string $field
     * @param array $in
     * @return $this
     */
    public function whereIn($field = "", $in = array())
    {
        $this->_where_init($field);
        $this->wheres[$field]['$in'] = $in;
        return $this;
    }

    /**
     * 增加IN ALL条件
     *
     * @param string $field
     * @param array $in
     * @return $this
     */
    public function whereInAll($field = "", $in = array())
    {
        $this->_where_init($field);
        $this->wheres[$field]['$all'] = $in;
        return $this;
    }

    /**
     * 增加NOT IN条件
     * @param string $field
     * @param array $in
     * @return $this
     */
    public function whereNotIn($field = "", $in = array())
    {
        $this->_where_init($field);
        $this->wheres[$field]['$nin'] = $in;
        return $this;
    }

    /**
     * 增加大于条件
     *
     * @param string $field
     * @param $x
     * @return $this
     */
    public function whereGt($field = "", $x)
    {
        $this->_where_init($field);
        $this->wheres[$field]['$gt'] = $x;
        return $this;
    }

    /**
     * 增加大于等于条件
     *
     * @param string $field
     * @param $x
     * @return $this
     */
    public function whereGte($field = "", $x)
    {
        $this->_where_init($field);
        $this->wheres[$field]['$gte'] = $x;
        return$this;
    }

    /**
     * 增加小于条件
     * @param string $field
     * @param $x
     * @return $this
     */
    public function whereLt($field = "", $x)
    {
        $this->_where_init($field);
        $this->wheres[$field]['$lt'] = $x;
        return$this;
    }

    /**
     * 增加小于等于条件
     *
     * @param string $field
     * @param $x
     * @return $this
     */
    public function whereLte($field = "", $x)
    {
        $this->_where_init($field);
        $this->wheres[$field]['$lte'] = $x;
        return $this;
    }

    /**
     * 增加范围条件
     *
     * @param string $field
     * @param $x
     * @param $y
     * @return $this
     */
    public function whereBetween($field = "", $x, $y)
    {
        $this->_where_init($field);
        $this->wheres[$field]['$gte'] = $x;
        $this->wheres[$field]['$lte'] = $y;
        return $this;
    }

    /**
     * 增加范围不包含自身条件
     * @param string $field
     * @param $x
     * @param $y
     * @return $this
     */
    public function whereBetweenNe($field = "", $x, $y)
    {
        $this->_where_init($field);
        $this->wheres[$field]['$gt'] = $x;
        $this->wheres[$field]['$lt'] = $y;
        return $this;
    }


    /**
     * 添加取反条件
     * @param string $field
     * @param $x
     * @return $this
     */
    public function whereNe($field = '', $x)
    {
        $this->_where_init($field);
        $this->wheres[$field]['$ne'] = $x;
        return $this;
    }

    /**
     *	--------------------------------------------------------------------------------
     *	LIKE PARAMETERS
     *	--------------------------------------------------------------------------------
     *
     *	Get the documents where the (string) value of a $field is like a value. The defaults
     *	allow for a case-insensitive search.
     *
     *	@param $flags
     *	Allows for the typical regular expression flags:
     *		i = case insensitive
     *		m = multiline
     *		x = can contain comments
     *		l = locale
     *		s = dotall, "." matches everything, including newlines
     *		u = match unicode
     *
     *	@param $enable_start_wildcard
     *	If set to anything other than TRUE, a starting line character "^" will be prepended
     *	to the search value, representing only searching for a value at the start of
     *	a new line.
     *
     *	@param $enable_end_wildcard
     *	If set to anything other than TRUE, an ending line character "$" will be appended
     *	to the search value, representing only searching for a value at the end of
     *	a line.
     *
     *	@usage : $this->mongo_db->like('foo', 'bar', 'im', FALSE, TRUE);
     *
     */
    public function like($field = "", $value = "", $flags = "i", $enable_start_wildcard = TRUE, $enable_end_wildcard = TRUE)
    {
        $field = (string) trim($field);
        $this->where_init($field);
        $value = (string) trim($value);
        $value = quotemeta($value);

        if ($enable_start_wildcard !== TRUE)
        {
            $value = "^" . $value;
        }

        if ($enable_end_wildcard !== TRUE)
        {
            $value .= "$";
        }

        $regex = "/$value/$flags";
        $this->wheres[$field] = new MongoRegex($regex);
        return $this;
    }

    public function orderBy($fields = array())
    {
        foreach ($fields as $col => $val)
        {
            if ($val == -1 || $val === FALSE || strtolower($val) == 'desc')
            {
                $this->sorts[$col] = -1;
            }
            else
            {
                $this->sorts[$col] = 1;
            }
        }
        return $this;
    }


    public function limit($x = 99999)
    {
        if ($x !== NULL && is_numeric($x) && $x >= 1)
        {
            $this->limit = (int) $x;
        }
        return $this;
    }


    public function offset($x = 0)
    {
        if ($x !== NULL && is_numeric($x) && $x >= 1)
        {
            $this->offset = (int) $x;
        }
        return $this;
    }


    public function getWhere($collection = "", $where = array())
    {
        return $this->where($where)->get($collection);
    }


    public function execute($code = '')
    {
        $returns = array();

        $returns = $this->db->execute($code);

        return $returns;
    }


    public function get($collection = '')
    {
        $documents = $this->db
            ->{$collection}
            ->find($this->wheres, $this->selects)
            ->limit((int)$this->limit)
            ->skip((int)$this->offset)
            ->sort($this->sorts);

        $this->clear();

        $returns = array();

        foreach ($documents as $doc) {
            $returns[] = $doc;
        }

        return $returns;
    }


    public function count($collection = '')
    {
        $count = $this->db
            ->{$collection}
            ->find($this->wheres, $this->selects)
            ->limit((int)$this->limit)
            ->skip((int)$this->offset)
            ->count();

        $this->clear();

        return $count;
    }


    public function insert($collection = '', $insert = array())
    {
        try {
            $this->db->{$collection}->insert($insert, array($this->querySafety => TRUE));
            if (isset($insert['_id'])) {
                return $insert['_id'];
            } else {
                return FALSE;
            }
        } catch (MongoCursorException $e) {
            Sys_Common::showError("Insert of Data Into MongoDB failed:{$e->getMessage()}", 'MongoDB');
        }
    }


    public function update($collection = '', $data = array(), $options = array())
    {
        try {
            $options = array_merge($options, array($this->querySafety => TRUE, 'multiple' => FALSE));
            $this->db->{$collection}->update($this->wheres, array('$set' => $data), $options);
            $this->clear();
            return TRUE;
        } catch (MongoCursorException $e) {
            Sys_Common::showError("Update of data into MongoDB failed:{$e->getMessage()}", 'MongoDB');
        }
    }


    public function updateAll($collection = '', $data = array())
    {
        try {
            $options = array($this->querySafety => TRUE, 'multiple' => TRUE);
            $this->db->{$collection}->update($this->wheres, array('$set' => $data), $options);
            $this->clear();
            return TRUE;
        } catch (MongoCursorException $e) {
            Sys_Common::showError("Update of data into MongoDB failed:{$e->getMessage()}", 'MongoDB');
        }
    }


    public function delete($collection = '')
    {
        try
        {
            $this->db->{$collection}->remove($this->wheres, array($this->querySafety => TRUE, 'justOne' => TRUE));
            $this->clear();
            return TRUE;
        }
        catch (MongoCursorException $e)
        {
            Sys_Common::showError("Delete of data into MongoDB failed: {$e->getMessage()}", 'MongoDB');
        }
    }


    public function deleteAll($collection = '')
    {
        try
        {
            $this->db->{$collection}->remove($this->wheres, array($this->querySafety => TRUE, 'justOne' => FALSE));
            $this->clear();
            return TRUE;
        }
        catch (MongoCursorException $e)
        {
            Sys_Common::showError("Delete of data into MongoDB failed: {$e->getMessage()}", 'MongoDB');
        }
    }


    public function clear()
    {
        $this->selects	= array();
        $this->wheres	= array();
        $this->limit	= 999999;
        $this->offset	= 0;
        $this->sorts	= array();
    }


    private function _where_init($param)
    {
        if ( ! isset($this->wheres[$param]))
        {
            $this->wheres[ $param ] = array();
        }
    }
}