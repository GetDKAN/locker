<?php

namespace LockerTest;

class LockerTest extends \PHPUnit\Framework\TestCase
{
    private $locker;

    protected function setUp(): void
    {
        $this->locker = new \Locker\Locker("test", 5, 1);
    }

    public function testReleaseAndWait()
    {
        $this->assertTrue($this->locker->getLock());

        try {
            $this->locker->getLock();
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), "The lock was not acquire after 1 second(s).");
        }

        $this->locker->releaseLock();
        $this->assertTrue($this->locker->getLock());
    }

    public function testExpiration()
    {
        $this->assertTrue($this->locker->getLock());
        sleep(6);
        $this->assertTrue($this->locker->getLock());
    }

    protected function tearDown(): void
    {
          $this->locker->releaseLock();
    }
}
