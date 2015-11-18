<?php

namespace phpspec\Rawkode\Eidetic\EventSourcing\InMemoryEventStore;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Rawkode\Eidetic\EventSourcing\EventSourcedEntity;

class InMemoryEventStoreSpec extends ObjectBehavior
{
    function let(EventSourcedEntity $eventSourcedEntity)
    {
        // Seed our EventStore with two events for identifier "my-identifier-seed"
        $eventSourcedEntity->identifier()->willReturn('my-identifier-seed');
        $eventSourcedEntity->version()->willReturn(0);
        $eventSourcedEntity->stagedEvents()->willReturn([new \stdClass, new \stdClass]);

        $this->save($eventSourcedEntity);
    }

    function it_implments_the_event_store_interface()
    {
        $this->shouldHaveType('Rawkode\Eidetic\EventSourcing\EventStore\EventStore');
    }

    function it_can_save_an_event_sourced_entity(EventSourcedEntity $eventSourcedEntity)
    {
        $eventSourcedEntity->identifier()->willReturn('my-identifier-save');
        $eventSourcedEntity->version()->willReturn(0);
        $eventSourcedEntity->stagedEvents()->willReturn([new \stdClass, new \stdClass]);

        $this->shouldNotThrow('Rawkode\Eidetic\EventSourcing\EventStore\VersionMismatchException')->during('save', [ $eventSourcedEntity ]);
    }

    function it_can_fetch_an_entities_events()
    {
        $this->shouldNotThrow('EntityDoesNotExist')->during('fetchEntityEvents', [ 'my-identifier-seed' ]);
        $this->fetchEntityEvents('my-identifier-seed')->shouldHaveCount(2);
    }

    function it_should_throw_version_mismatch_exception_when_entity_is_at_wrong_version(EventSourcedEntity $eventSourcedEntity)
    {
        $eventSourcedEntity->identifier()->willReturn('my-identifier-seed');
        $eventSourcedEntity->version()->willReturn(0);
        $eventSourcedEntity->stagedEvents()->willReturn([new \stdClass, new \stdClass]);

        // This eventSourcedEntity should now be on the incorrect version and throw the error
        $this->shouldThrow('Rawkode\Eidetic\EventSourcing\EventStore\VersionMismatchException')->during('save', [ $eventSourcedEntity ]);
    }

    function it_should_throw_entity_does_not_exist_exception_when_entity_does_not_exist()
    {
        $this->shouldThrow('Rawkode\Eidetic\EventSourcing\EventStore\EntityDoesNotExistException')->during('fetchEntityEvents', [ 0 ]);
    }

    function it_should_throw_invalid_event_exception_when_event_is_not_a_class(EventSourcedEntity $eventSourcedEntity)
    {
        $eventSourcedEntity->identifier()->willReturn('my-identifier-new');
        $eventSourcedEntity->version()->willReturn(0);
        $eventSourcedEntity->stagedEvents()->willReturn([new \stdClass, [], new \stdClass]);

        // This eventSourcedEntity should now be on the incorrect version and throw the error
        $this->shouldThrow('Rawkode\Eidetic\EventSourcing\InvalidEventException')->during('save', [ $eventSourcedEntity ]);
    }

    function it_can_rollback_when_a_transaction_is_aborted(EventSourcedEntity $eventSourcedEntity)
    {
        $eventSourcedEntity->identifier()->willReturn('my-identifier-new');
        $eventSourcedEntity->version()->willReturn(0);
        $eventSourcedEntity->stagedEvents()->willReturn([new \stdClass, new \stdClass, [ ]]);

        $this->shouldThrow('Rawkode\Eidetic\EventSourcing\InvalidEventException')->during('save', [ $eventSourcedEntity ]);
        $this->shouldThrow('Rawkode\Eidetic\EventSourcing\EventStore\EntityDoesNotExistException')->during('fetchEntityEvents', [ 'my-identifier-new' ]);
    }
}