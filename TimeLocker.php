<?php
/**
 * User: Ivan
 */

namespace fwext\ScriptLock;

class TimeLocker extends Locker {
    /**
     * @var integer Время блокировки
     */
    private $time;

    /**
     * @param $name Имя блокировки
     * @param $time Время блокировки
     */
    function __construct($name, $time) {
        $this->time = $time;

        parent::__construct($name);
    }

    /**
     * @return Время блокировки|int
     */
    public function getTime() { return $this->time; }

    /**
     * Проверка существования блокировки
     *
     * @return bool
     */
    public function isLock() {
        if(file_exists($this->getLockFilePath())) {
            if(time() - filectime($this->getLockFilePath()) > $this->getTime()) {
                $this->delete();

                return false;
            }

            return true;
        }

        return false;
    }
} 