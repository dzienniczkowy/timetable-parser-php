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
        $days  = $this->getDays($table->find('tr th'));

        $this->setLessonHoursToDays($table, $days);

        $title = explode(' ', $this->doc->find('title')->text());

        return [
            'name' => $this->doc->find('.tytulnapis')->text(),
            'typeName' => $title[2],
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
        $hour  = [
            'number'  => $rowCells->get(0)->textContent,
            'start'   => trim($hours[0]),
            'end'     => trim($hours[1]),
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
                'teacher'   => ['name' => '', 'value' => ''],
                'room'      => ['name' => '', 'value' => ''],
                'className' => ['name' => '', 'value' => ''],
                'subject'   => '',
                'diversion' => false,
                'alt'       => $current->text(),
            ];
        }

        return $hour;
    }

    private function getLesson(Element $cell): array
    {
        $teacher   = $cell->findXPath('./*[@class="n"]');
        $room      = $cell->findXPath('./*[@class="s"]');
        $className = $cell->findXPath('./*[@class="o"]');
        $subject   = $cell->findXPath('./*[@class="p"]');

        $lesson = [
            'teacher'   => [
                'name'  => $teacher->text(),
                'value' => $this->getUrlValue($teacher->first(), 'n'),
            ],
            'room'      => [
                'name'  => $room->text(),
                'value' => $this->getUrlValue($room->first(), 's'),
            ],
            'className' => [
                'name'  => $className->text(),
                'value' => $this->getUrlValue($className->first(), 'o'),
            ],
            'subject'   => $subject->text(),
            'diversion' => false,
            'alt'       => trim($cell->findXPath('./text()')->text()),
        ];

        if ($cell->findXPath('./*[@class="p"]')->count() > 1) {
            $lesson['subject'] = $subject->first()->text()
                .trim($cell->findXPath('./text()[(preceding::*[@class="p"])]')->text())
                .' '.$subject->end()->text();
        }

        return $lesson;
    }

    private function getUrlValue(?Element $el, string $prefix): string
    {
        if (null === $el) {
            return '';
        }

        return str_replace([$prefix, '.html'], '', $el->attr('href'));
    }
}
