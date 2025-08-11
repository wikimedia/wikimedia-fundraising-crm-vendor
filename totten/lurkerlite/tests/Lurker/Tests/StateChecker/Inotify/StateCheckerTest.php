<?php

namespace Lurker\Tests\StateChecker\Inotify;

use Lurker\Event\FilesystemEvent;
use Lurker\Resource\ResourceInterface;
use Lurker\StateChecker\Inotify\CheckerBag;

abstract class StateCheckerTest extends \PHPUnit\Framework\TestCase
{
    protected $bag;
    protected $resource;
    private $exists = true;

    public function setUp()
    {
        if (!function_exists('inotify_init')) {
            $this->markTestSkipped('Inotify is required for this test');
        }

        $this->bag      = new CheckerBag('whatever');
        $this->resource = $this->getResource();
    }

    public function testResourceAddedToBag()
    {
        $this->setAddWatchReturns(1);
        $checker = $this->getChecker();

        $this->assertCount(1, $this->bag->get(1));
        $this->assertContains($checker, $this->bag->get(1));
    }

    public function testResourceDeleted()
    {
        $this->setAddWatchReturns(1);
        $this->markResourceNonExistent();
        $checker = $this->getChecker();
        $checker->setEvent(IN_IGNORED);

        $events = $checker->getChangeset();

        $this->assertHasEvent($this->resource, FilesystemEvent::DELETE, $events);
        $this->assertCount(0, $this->bag->get(1));
        $this->assertNull($checker->getId());
    }

    public function testResourceCreated()
    {
        $this->setAddWatchReturns(1);
        $this->markResourceNonExistent();
        $checker = $this->getChecker();
        $checker->setEvent(IN_IGNORED);

        $checker->getChangeset();

        $this->setAddWatchReturns(2);
        $this->markResourceExistent();
        $events = $checker->getChangeset();

        $this->assertHasEvent($this->resource, FilesystemEvent::CREATE, $events);
        $this->assertCount(1, $this->bag->get(2));
        $this->assertContains($checker, $this->bag->get(2));
    }

    public function testResourceMoved()
    {
        $this->setAddWatchReturns(1);
        $this->markResourceNonExistent();
        $checker = $this->getChecker();
        $checker->setEvent(IN_MOVE_SELF);

        $events = $checker->getChangeset();

        $this->assertHasEvent($this->resource, FilesystemEvent::DELETE, $events);
        $this->assertCount(0, $this->bag->get(1));
        $this->assertNull($checker->getId());

        $this->setAddWatchReturns(2);
        $this->markResourceExistent();
        $events = $checker->getChangeset();

        $this->assertHasEvent($this->resource, FilesystemEvent::CREATE, $events);
        $this->assertCount(1, $this->bag->get(2));
        $this->assertContains($checker, $this->bag->get(2));
    }

    public function testResourceMovedAndReturnedSameWatchId()
    {
        $this->setAddWatchReturns(1);
        $checker = $this->getChecker();
        $checker->setEvent(IN_MOVE_SELF);

        $events = $checker->getChangeset();
        $this->assertEmpty($events);
    }

    public function isResourceExists()
    {
        return $this->exists;
    }

    protected function assertHasEvent(ResourceInterface $resource, $event, $events)
    {
        $this->assertContains(array('resource' => $resource, 'event' => $event), $events, sprintf('Cannot find the expected event for the received resource'));
    }

    protected function markResourceExistent()
    {
        $this->exists = true;
    }

    protected function markResourceNonExistent()
    {
        $this->exists = false;
    }

    abstract protected function setAddWatchReturns($id);
    abstract protected function getChecker();
    abstract protected function getResource();
}
