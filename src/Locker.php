<?php

namespace Locker;

class Locker
{

    private $timeWaited = -1;
    private $name;
    private $expire;
    private $wait;

  /**
   * Constructor.
   *
   * @param string $name
   *   The locks name.
   *
   * @param int $expire
   *   When the lock is set, it will automatically expire after
   *   this amount of time has passed if the lock is not released.
   *
   * @param int $wait
   *   Time we are willing to wait for the lock before we bail.
   */
    public function __construct($name, $expire = 30, $wait = 5)
    {
        $this->name = $name;
        $this->expire =  $expire;
        $this->wait = $wait;
    }

  /**
   * Wait until we get the lock, or we get tired of waiting.
   */
    public function getLock()
    {
        do {
            $this->wait();
            if ($this->timeWaited > $this->wait) {
                $this->timeWaited = -1;
                throw new \Exception("The lock was not acquire after {$this->wait} second(s).");
            }
            $this->lockExpired();
        } while (!$this->lockCreated());

        $this->timeWaited = -1;
        return true;
    }

  /**
   * Release the lock.
   */
    public function releaseLock()
    {
        $path = "/tmp/{$this->name}.lock";
        if (file_exists($path)) {
            array_map('unlink', glob("{$path}/*.*"));
            rmdir($path);
        }
    }

    private function wait()
    {
        if ($this->timeWaited >= 0) {
            sleep(1);
        }
        $this->timeWaited++;
    }

    private function lockExpired()
    {
        $file = "/tmp/{$this->name}.lock/expire.txt";
        if (file_exists($file) && file_get_contents($file) < time()) {
            $this->releaseLock();
            return true;
        }
        return false;
    }

    private function lockCreated()
    {
        $lock_path = "/tmp/{$this->name}.lock";
        if (@mkdir($lock_path, 0700)) {
            file_put_contents("{$lock_path}/expire.txt", (time() + $this->expire));
            return true;
        }
        return false;
    }
}
