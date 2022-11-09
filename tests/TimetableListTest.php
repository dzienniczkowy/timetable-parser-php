<?php

namespace Wulkanowy\Tests\TimetableParser;

use PHPUnit\Framework\TestCase;
use Wulkanowy\TimetableParser\TimetableList;

class TimetableListTest extends TestCase
{
    private TimetableList $select;

    private TimetableList $list;

    private TimetableList $expandable;

    public function setUp(): void
    {
        $this->select = new TimetableList(file_get_contents(__DIR__.'/fixtures/lista-form.html'));
        $this->list = new TimetableList(file_get_contents(__DIR__.'/fixtures/lista-unordered.html'));
        $this->expandable = new TimetableList(file_get_contents(__DIR__.'/fixtures/lista-expandable.html'));
    }

    public function testGetListUrl(): void
    {
        $index = new TimetableList(file_get_contents(__DIR__.'/fixtures/index.html'));
        $this->assertEquals('lista.html', $index->getListUrl());
    }

    public function testClassDataSelect(): void
    {
        $this->assertCount(2, $this->select->getTimetableList()['classes']);
        $this->assertEquals('1Tc', $this->select->getTimetableList()['classes'][0]['name']);
        $this->assertEquals('1', $this->select->getTimetableList()['classes'][0]['value']);
        $this->assertEquals('1Ti', $this->select->getTimetableList()['classes'][1]['name']);
        $this->assertEquals('2', $this->select->getTimetableList()['classes'][1]['value']);
    }

    public function testClassDataUl(): void
    {
        $this->assertCount(2, $this->list->getTimetableList()['classes']);
        $this->assertEquals('1Tc', $this->list->getTimetableList()['classes'][0]['name']);
        $this->assertEquals('1', $this->list->getTimetableList()['classes'][0]['value']);
        $this->assertEquals('1Ti', $this->list->getTimetableList()['classes'][1]['name']);
        $this->assertEquals('2', $this->list->getTimetableList()['classes'][1]['value']);
    }

    public function testClassDataExpandable(): void
    {
        $this->assertCount(2, $this->expandable->getTimetableList()['classes']);
        $this->assertEquals('1Tc', $this->expandable->getTimetableList()['classes'][0]['name']);
        $this->assertEquals('1', $this->expandable->getTimetableList()['classes'][0]['value']);
        $this->assertEquals('1Ti', $this->expandable->getTimetableList()['classes'][1]['name']);
        $this->assertEquals('2', $this->expandable->getTimetableList()['classes'][1]['value']);
    }

    public function testTeachersDataSelect(): void
    {
        $this->assertCount(2, $this->select->getTimetableList()['teachers']);
        $this->assertEquals('I.Ochocki (Io)', $this->select->getTimetableList()['teachers'][0]['name']);
        $this->assertEquals('1', $this->select->getTimetableList()['teachers'][0]['value']);
        $this->assertEquals('M.Oleszkiewicz (Mo)', $this->select->getTimetableList()['teachers'][1]['name']);
        $this->assertEquals('3', $this->select->getTimetableList()['teachers'][1]['value']);
    }

    public function testTeachersDataUl(): void
    {
        $this->assertCount(2, $this->list->getTimetableList()['teachers']);
        $this->assertEquals('I.Ochocki (Io)', $this->list->getTimetableList()['teachers'][0]['name']);
        $this->assertEquals('1', $this->list->getTimetableList()['teachers'][0]['value']);
        $this->assertEquals('M.Oleszkiewicz (Mo)', $this->list->getTimetableList()['teachers'][1]['name']);
        $this->assertEquals('3', $this->list->getTimetableList()['teachers'][1]['value']);
    }

    public function testTeachersDataExpandable(): void
    {
        $this->assertCount(2, $this->expandable->getTimetableList()['teachers']);
        $this->assertEquals('I.Ochocki (Io)', $this->expandable->getTimetableList()['teachers'][0]['name']);
        $this->assertEquals('1', $this->expandable->getTimetableList()['teachers'][0]['value']);
        $this->assertEquals('M.Oleszkiewicz (Mo)', $this->expandable->getTimetableList()['teachers'][1]['name']);
        $this->assertEquals('3', $this->expandable->getTimetableList()['teachers'][1]['value']);
    }

    public function testRoomDataSelect(): void
    {
        $this->assertCount(2, $this->select->getTimetableList()['rooms']);
        $this->assertEquals('16 prac. geograficzna', $this->select->getTimetableList()['rooms'][0]['name']);
        $this->assertEquals('1', $this->select->getTimetableList()['rooms'][0]['value']);
        $this->assertEquals('17 prac. fizyczna', $this->select->getTimetableList()['rooms'][1]['name']);
        $this->assertEquals('2', $this->select->getTimetableList()['rooms'][1]['value']);
    }

    public function testRoomDataUl(): void
    {
        $this->assertCount(2, $this->list->getTimetableList()['rooms']);
        $this->assertEquals('16 prac. geograficzna', $this->list->getTimetableList()['rooms'][0]['name']);
        $this->assertEquals('1', $this->list->getTimetableList()['rooms'][0]['value']);
        $this->assertEquals('17 prac. fizyczna', $this->list->getTimetableList()['rooms'][1]['name']);
        $this->assertEquals('2', $this->list->getTimetableList()['rooms'][1]['value']);
    }

    public function testRoomDataExpandable(): void
    {
        $this->assertCount(2, $this->expandable->getTimetableList()['rooms']);
        $this->assertEquals('16 prac. geograficzna', $this->expandable->getTimetableList()['rooms'][0]['name']);
        $this->assertEquals('1', $this->expandable->getTimetableList()['rooms'][0]['value']);
        $this->assertEquals('17 prac. fizyczna', $this->expandable->getTimetableList()['rooms'][1]['name']);
        $this->assertEquals('2', $this->expandable->getTimetableList()['rooms'][1]['value']);
    }
}
