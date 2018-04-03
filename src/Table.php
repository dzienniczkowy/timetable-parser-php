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

        $title = explode(' ', $this->doc->find('title')->text());
        $generated = preg_split('/\s+/', trim($this->doc->find('td[align=right]')->end()->text()));

        return [
            'name'        => $this->doc->find('.tytulnapis')->text(),
            'generated'   => trim($generated[1]),
            'description' => trim($this->doc->find('td[align=left]')->first()->text()),
            'typeName'    => $title[2],
            'days'        => $days,
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
            'number'  => $rowCells->get(0)->textContent,
            'start'   => trim($hours[0]),
            'end'     => trim($hours[1]),
            'lessons' => [],
        ];

        /** @var Element $current */
        $current = $rowCells->get($index);

        $hour['lessons'] = $this->getExtractedLessons($current);

        $className = $current->findXPath('./*[@class="o"]');
        if ($className->count() > 1) {
            unset(
                $hour['lessons'][\count($hour['lessons']) - 1]['className'],
                $hour['lessons'][\count($hour['lessons']) - 1]['alt']
            );

            /** @var Element $item */
            foreach (explode(',', $current->getOuterHtml()) as $item) {
                $doc = new Document();
                $doc->html(str_replace('<td class="l">', '', $item));
                $el = $doc->find('body')->first();
                $hour['lessons'][\count($hour['lessons']) - 1]['className'][] = [
                    'name'  => trim($el->find('a.o')->text()),
                    'value' => $this->getUrlValue($el->find('a.o')->first(), 'o'),
                    'alt'   => trim($el->findXPath('./text()')->text()),
                ];
            }
        }

        if (\count($hour['lessons']) === 0 && trim($current->text(), "\xC2\xA0\n") !== '') {
            $hour['lessons'][] = [
                'teacher'   => ['name' => '', 'value' => ''],
                'room'      => ['name' => '', 'value' => ''],
                'className' => ['name' => '', 'value' => ''],
                'subject'   => '',
                'diversion' => false,
                'alt'       => trim($current->text()),
            ];
        }

        return $hour;
    }

    private function getExtractedLessons(Element $current): array
    {
        $lessons = [];

        $chunks = explode('<br>', $current->getOuterHtml());
        $spans = $current->find('span[style]');
        $subject = $current->findXPath('./*[@class="p"]');

        if ($spans->count() === 0 && $subject->count() > 0 & \count($chunks) === 0) { // simple one lesson in hour without division
            $lessons[] = $this->getLesson($current);
        } elseif ($spans->count() > 0 && $subject->count() === 0) { // simply two or more groups with division
            foreach ($spans as $group) {
                $lessons[] = array_merge($this->getLesson($group), ['diversion' => true]);
            }
        } elseif ($subject->count() > 0 && \count($chunks) > 0) {
            foreach ($chunks as $item) {
                $doc = new Document();
                $doc->html($item);

                $span = $doc->find('span[style]');
                $cell = $doc->find('.l');
                $body = $doc->find('body');
                if ($span->count() > 0) {
                    $lessons[] = array_merge($this->getLesson($span->first()), ['diversion' => true]);
                } elseif ($cell->count() > 0) {
                    $lessons[] = $this->getLesson($cell->first());
                } elseif ($body->count() > 0) {
                    $lessons[] = $this->getLesson($body->first());
                }
            }
        }

        return $lessons;
    }

    private function getLesson(Element $cell): array
    {
        $teacher = $cell->findXPath('./*[@class="n"]');
        $room = $cell->findXPath('./*[@class="s"]');
        $className = $cell->findXPath('./*[@class="o"]');
        $subject = $cell->findXPath('./*[@class="p"]');

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
