<?php
/**
 * User: Ivan Lutokhin
 */

namespace fwext\ScriptLock;

use Yii;

class Locker {
    const EXTENSION = ".lock";

    /**
     * @var string Имя блокировки
     */
    private $name;

    /**
     * @var string Путь к директории с файлами блокировок
     */
    private $directory;

    /**
     * @param $name Имя блокировки
     */
    function __construct($name) {
        $this->name = $name;

        $this->directory = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'lock';

        if(!is_dir($this->directory)) {
            mkdir($this->directory, 0777);
        }
    }

    /**
     * @return Имя блокировки|string
     */
    public function getName() { return $this->name; }

    /**
     * @return string
     */
    public function getDirectory() { return $this->directory; }

    /**
     * @return Путь к файлу блокировки|string
     */
    public function getLockFilePath() { return $this->directory . DIRECTORY_SEPARATOR . $this->name . Locker::EXTENSION; }

    /**
     * Проверка существования блокировки
     *
     * @return bool
     */
    public function isLock() {
        if(file_exists($this->getLockFilePath())) {
            $pid = (int)file_get_contents($this->getLockFilePath());

            return file_exists("/proc/$pid");
        }

        return false;
    }

    /**
     * Создание блокировки
     *
     * @return bool
     */
    public function create() {
        if($this->isLock()) {
            return false;
        }

        file_put_contents($this->getLockFilePath(), getmypid(), LOCK_EX);

        return true;
    }

    /**
     * Удаление блокировки
     *
     * @return bool
     */
    public function delete() {
        if(!$this->isLock()) {
            return false;
        }

        return unlink($this->getLockFilePath());
    }

    /**
     * Ожидание снятия блокировки
     *
     * @param int $timeout Таймаут ожидания снятия блокировки
     * @return bool
     */
    public function wait($timeout = 0) {
        $wait = 0;

        while(true) {
            if($this->isLock()) {
                sleep(1);

                $wait++;
            } else {
                return true;
            }

            if($wait > $timeout) break;
        }

        return false;
    }
}