<?php
declare(strict_types=1);

define("BASEROOT", $_SERVER['DOCUMENT_ROOT']);
require('database/app/lib/Base/Base.php');

use Lib\Base;

final class BaseTest extends PHPUnit\Framework\TestCase
{
    public function testSpeedZero()
    {
        Base::clear();
        Base::setSpeed(0);
        $datetime1 = Base::getDateTime();
        sleep(5);
        $datetime2 = Base::getDateTime();
        $this->assertEquals($datetime1, $datetime2);
    }
    public function testSpeedOne()
    {
        Base::clear();
        $datetime1 = Base::getDateTime();
        sleep(5);
        $datetime2 = Base::getDateTime();
        $this->assertEquals($datetime1->getTimeStamp() + 5, $datetime2->getTimeStamp());
    }
    public function testSpeedOneThousand()
    {
        Base::clear();
        Base::setSpeed(1000);
        $datetime1 = Base::getDateTime();
        sleep(5);
        $datetime2 = Base::getDateTime();
        $this->assertEquals($datetime1->getTimeStamp() + 5000, $datetime2->getTimeStamp());
    }

    public function testSpeedStillOneThousand()
    {
        $datetime1 = Base::getDateTime();
        sleep(5);
        $datetime2 = Base::getDateTime();
        $this->assertEquals($datetime1->getTimeStamp() + 5000, $datetime2->getTimeStamp());
    }

    public function testSetDateTime()
    {
        $datetime0 = new DateTime('2019-01-01T00:00:00Z');
        Base::setDateTime($datetime0);
        $datetime1 = Base::getDateTime();
        $this->assertEquals($datetime0->getTimeStamp(), $datetime1->getTimeStamp());
        sleep(5);
        $datetime2 = Base::getDateTime();
        $this->assertEquals($datetime1->getTimeStamp() + 5000, $datetime2->getTimeStamp());
    }
}
