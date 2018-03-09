<?php
/**
 * Class ShareMemoryDao
 */
class Dao_ShareMemory {

    /**
     * Ipc key
     * @var int
     */
    public $id;

    /**
     * Shared memory segment identifier
     * @var resources
     */
    public $shmId;

    /**
     * Holds the default permission (octal) that will be used in created memory blocks
     * @var int
     */
    public $perms = 0644;

    /**
     * Shared memory block instantiation
     * @param $area
     */
    public function __construct($area = null)
    {
        if($area === null) {
            $this->id = self::generateId('a');
        } else {
            $this->id = self::generateId($area);
        }

        $this->shmId = @shmop_open($this->id, "a", 0666, 0);
    }

    /**
     * Generates ipc key
     * @param $flag
     * @return int
     */
    public static function generateId($flag)
    {
        $id = ftok(__FILE__, $flag);

        return $id;
    }

    /**
     * Generate data size
     * @param $data
     * @return int
     */
    public static function generateSize($data)
    {
        $data = serialize($data);

        //当shm_put_var调用时，php会在序列化后的数据前面，加一个header
        $headerSize = (PHP_INT_SIZE * 4) + 8;
        $dataSize   = (((strlen($data) + (4 * PHP_INT_SIZE)) / 4 ) * 4 ) + 4;

        $size = $headerSize + $dataSize;

        return $size;
    }

    /**
     * Whether exists
     * @param $id
     * @return bool
     */
    public function exists($id)
    {
        $returns = false;

        $this->shmId = @shmop_open($id, "a", 0666, 0);

        if (!empty($this->shmId)) {
            $returns = true;
        }

        return $returns;
    }

    public function put($data)
    {
        $size = self::generateSize($data);

        $exists = $this->exists($this->id);

        if ($exists) {
            shmop_delete($this->shmId);
            shmop_close($this->shmId);
            $this->shmId = shmop_open($this->id, "c", $this->perms, $size);
            shmop_write($this->shmId, serialize($data), 0);
        } else {
            $this->shmId = shmop_open($this->id, "c", $this->perms, $size);
            shmop_write($this->shmId, serialize($data), 0);
        }
    }

    public function get()
    {
        $data = null;

        $exists = $this->exists($this->id);

        if ($exists) {
            $size = shmop_size($this->shmId);
            $data = unserialize(shmop_read($this->shmId, 0, $size));
        }

        return $data;

    }

    /**
     * Remove
     */
    public function remove()
    {
        $exists = $this->exists($this->id);

        if ($exists) {
            shmop_delete($this->shmId);
            shmop_close($this->shmId);
        }
    }

    public static function lock($key = 10000)
    {
        $semId = sem_get($key);

        sem_acquire($semId);

        return $semId;
    }

    public static function unlock($semId)
    {
        sem_release($semId);
        sem_remove($semId);
    }

    /**
     * Closes the shared memory block and stops manipulation
     *
     * @access public
     */
    public function __destruct()
    {
        $exists = $this->exists($this->id);

        if ($exists) {
            shmop_close($this->shmId);
        }
    }

}