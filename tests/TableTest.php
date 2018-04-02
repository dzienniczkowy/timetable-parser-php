<?php

namespace Wulkanowy\Tests\TimetableParser;

use PHPUnit\Framework\TestCase;
use Wulkanowy\TimetableParser\Table;

class TableTest extends TestCase
{
    private $table;

    private $tableRoom;

    public function setUp()
    {
        $this->table = new Table(file_get_contents(__DIR__.'/fixtures/oddzial.html'));
        $this->tableRoom = new Table(file_get_contents(__DIR__.'/fixtures/sala.html'));
    }

    public function testGetTable(): void
    {
        $this->assertCount(5, $this->table->getTable()['days']);
        $this->assertCount(9, $this->table->getTable()['days'][2]['hours']);
    }

    public function testGetTableName(): void
    {
        $this->assertEquals('3Ti', $this->table->getTable()['name']);
        $this->assertEquals('21 prac. historii', $this->tableRoom->getTable()['name']);
    }

    public function testGetTableDays(): void
    {
        $this->assertEquals('Poniedziałek', $this->table->getTable()['days'][0]['name']);
        $this->assertEquals('Piątek', $this->table->getTable()['days'][4]['name']);
    }

    public function testGetTableHoursSize(): void
    {
        $this->assertCount(0, $this->table->getTable()['days'][1]['hours'][0]['lessons']);
        $this->assertCount(1, $this->table->getTable()['days'][1]['hours'][4]['lessons']);
        $this->assertCount(2, $this->table->getTable()['days'][2]['hours'][3]['lessons']);
        $this->assertCount(2, $this->table->getTable()['days'][4]['hours'][7]['lessons']);
    }

    public function testGetTableHoursNumber(): void {
        $this->assertEquals('1', $this->table->getTable()['days'][0]['hours'][0]['number']);
        $this->assertEquals('3', $this->table->getTable()['days'][1]['hours'][2]['number']);
        $this->assertEquals('5', $this->table->getTable()['days'][2]['hours'][4]['number']);
        $this->assertEquals('7', $this->table->getTable()['days'][3]['hours'][6]['number']);
        $this->assertEquals('9', $this->table->getTable()['days'][4]['hours'][8]['number']);
    }

    public function testGetTableHoursStart(): void
    {
        $this->assertEquals('8:00', $this->table->getTable()['days'][0]['hours'][0]['start']);
        $this->assertEquals('9:40', $this->table->getTable()['days'][1]['hours'][2]['start']);
        $this->assertEquals('11:30', $this->table->getTable()['days'][2]['hours'][4]['start']);
        $this->assertEquals('13:10', $this->table->getTable()['days'][3]['hours'][6]['start']);
        $this->assertEquals('14:50', $this->table->getTable()['days'][4]['hours'][8]['start']);
    }

    public function testGetTableHoursEnd(): void
    {
        $this->assertEquals('8:45', $this->table->getTable()['days'][0]['hours'][0]['end']);
        $this->assertEquals('10:25', $this->table->getTable()['days'][1]['hours'][2]['end']);
        $this->assertEquals('12:15', $this->table->getTable()['days'][2]['hours'][4]['end']);
        $this->assertEquals('13:55', $this->table->getTable()['days'][3]['hours'][6]['end']);
        $this->assertEquals('15:35', $this->table->getTable()['days'][4]['hours'][8]['end']);
    }

    public function testGetTableLessonTeacher(): void
    {
        $this->assertEquals('PR', $this->table->getTable()['days'][0]['hours'][0]['lessons'][0]['teacher']);
        $this->assertEquals('Dr', $this->table->getTable()['days'][0]['hours'][0]['lessons'][1]['teacher']);
        $this->assertEquals('Ho', $this->table->getTable()['days'][0]['hours'][4]['lessons'][0]['teacher']);
        $this->assertEquals('Oż', $this->table->getTable()['days'][1]['hours'][8]['lessons'][0]['teacher']);
        $this->assertEquals('', $this->table->getTable()['days'][1]['hours'][8]['lessons'][1]['teacher']);
    }

    public function testGetTableLessonRoom(): void
    {
        $this->assertEquals('33', $this->table->getTable()['days'][0]['hours'][0]['lessons'][0]['room']);
        $this->assertEquals('35', $this->table->getTable()['days'][0]['hours'][0]['lessons'][1]['room']);
        $this->assertEquals('21', $this->table->getTable()['days'][0]['hours'][4]['lessons'][0]['room']);
        $this->assertEquals('32', $this->table->getTable()['days'][1]['hours'][8]['lessons'][0]['room']);
        $this->assertEquals('19', $this->table->getTable()['days'][1]['hours'][8]['lessons'][1]['room']);
    }

    public function testGetTableLessonSubject(): void
    {
        $this->assertEquals('użytk.peryf-1/2', $this->table->getTable()['days'][0]['hours'][0]['lessons'][0]['subject']);
        $this->assertEquals('sieci.komput-2/2', $this->table->getTable()['days'][0]['hours'][0]['lessons'][1]['subject']);
        $this->assertEquals('u_hist.i sp.', $this->table->getTable()['days'][0]['hours'][4]['lessons'][0]['subject']);
        $this->assertEquals('naprawa.komp-1/2', $this->table->getTable()['days'][1]['hours'][8]['lessons'][0]['subject']);
        $this->assertEquals('r_fizyka-2/2 #3fi', $this->table->getTable()['days'][1]['hours'][8]['lessons'][1]['subject']);
    }

    public function testGetTableLessonClassName(): void
    {
        $this->assertEquals('2Tc', $this->tableRoom->getTable()['days'][0]['hours'][0]['lessons'][0]['className']);
        $this->assertEquals('4Tp', $this->tableRoom->getTable()['days'][1]['hours'][0]['lessons'][0]['className']);
    }

    public function testGetTableLessonClassAlt(): void
    {
        $this->assertEquals('', $this->table->getTable()['days'][0]['hours'][0]['lessons'][0]['alt']);
        $this->assertEquals('', $this->tableRoom->getTable()['days'][1]['hours'][0]['lessons'][0]['alt']);
        $this->assertEquals('Zajęcia praktyczne', $this->table->getTable()['days'][0]['hours'][8]['lessons'][0]['alt']);
    }
}
