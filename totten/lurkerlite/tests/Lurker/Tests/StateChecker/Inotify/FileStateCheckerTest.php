<?php

namespace Lurker\Tests\StateChecker\Inotify;

use Lurker\Event\FilesystemEvent;
use Lurker\Tests\StateChecker\Inotify\Fixtures\FileStateCheckerForTest;

class FileStateCheckerTest extends StateCheckerTest
{
    public function testResourceMovedAndReturnedDifferentWatchId()
    {
        $this->setAddWatchReturns(1);
        $checker = $this->getChecker();
        $checker->setEvent(IN_MOVE_SELF);

        $this->setAddWatchReturns(2);
        $events = $checker->getChangeset();

        $this->assertHasEvent($this->resource, FilesystemEvent::MODIFY, $events);
        $this->assertCount(0, $this->bag->get(1));
        $this->assertCount(1, $this->bag->get(2));
        $this->assertContains($checker, $this->bag->get(2));
    }

    protected function setAddWatchReturns($id)
    {
        FileStateCheckerForTest::setAddWatchReturns($id);
    }

    protected function getChecker()
    {
        return new FileStateCheckerForTest($this->bag, $this->resource);
    }

    protected function getResource()
    {
        $resource = $this
            ->getMockBuilder('Lurker\Resource\FileResource')
            ->disableOriginalConstructor()
            ->getMock();
        $resource
            ->expects($this->any())
            ->method('exists')
            ->will($this->returnCallback(array($this, 'isResourceExists')));

        return $resource;
    }
}
