<?php

namespace Wulkanowy\TimetableParser;

use DOMWrap\Document;
use DOMWrap\Element;
use DOMWrap\NodeList;

class Table
{
    private $doc;

    public function __construct(string $html)
    {
        $this->doc = new Document();
        $this->doc->html($html);
    }

    public function getTable(): array
    {
        $table = $this->doc->find('.tabela')->first();
        $days = $this->getDays($table->find('tr th'));

        $this->setLessonHoursToDays($table, $days);

        return [
            'name' => $this->doc->find('.tytulnapis')->text(),
            'days' => $days,
        ];
    }

    private function getDays(NodeList $headerCells): array
    {
        $headerCells->shift();
        $headerCells->shift();
        $days = [];

        /** @var \DOMNode $cell */
        foreach ($headerCells as $cell) {
            $days[] = ['name' => $cell->textContent];
        }

        return $days;
    }

    private function setLessonHoursToDays(Element $table, array &$days): void
    {
        $rows = $table->find('tr');
        $rows->shift();

        foreach ($rows as $row) {
            $rowCells = $row->find('td');

            // fill hours in day
            /** @var NodeList $rowCells */
            for ($i = 2; $i < $rowCells->count(); $i++) {
                $days[$i - 2]['hours'][] = $this->getHourWithLessons($rowCells, $i);
            }
        }
    }

    private function getHourWithLessons(NodeList $rowCells, int $index): array
    {
        $hours = explode('-', $rowCells->get(1)->textContent);
        $hour = [
            'number' => $rowCells->get(0)->textContent,
            'start' => trim($hours[0]),
            'end' => trim($hours[1]),
            'lessons' => [],
        ];

        /** @var Element $current */
        $current = $rowCells->get($index);

        foreach ($current->find('span[style]') as $group) {
            $hour['lessons'][] = array_merge($this->getLesson($group), ['diversion' => true]);
        }

        if ($current->findXPath('./*[@class="p"]')->count() !== 0) {
            $hour['lessons'][] = $this->getLesson($current);
        }

        if (\count($hour['lessons']) === 0 && trim($current->text(), "\xC2\xA0\n") !== '') {
            $hour['lessons'][] = [
                'teacher' => '',
                'subject' => '',
                'room' => '',
                'className' => '',
                'alt' => $current->text(),
            ];
        }

        return $hour;
    }

    private function getLesson(Element $cell): array
    {
        return [
            'teacher' => $cell->findXPath('./*[@class="n"]')->text(),
            'subject' => $cell->findXPath('./*[@class="p"]')->text(),
            'room' => $cell->findXPath('./*[@class="s"]')->text(),
            'className' => $cell->findXPath('./*[@class="o"]')->text(),
            'alt' => '',
            'diversion' => false,
        ];
    }
}
