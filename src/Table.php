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

        return [
            'number'  => $rowCells->get(0)->textContent,
            'start'   => trim($hours[0]),
            'end'     => trim($hours[1]),
            'lessons' => $this->getExtractedLessons($rowCells->get($index)),
        ];
    }

    private function getExtractedLessons(Element $current): array
    {
        $lessons = [];

        $chunks = explode('<br>', $current->getOuterHtml());
        $spans = $current->find('span[style]');
        $subject = $current->findXPath('./*[@class="p"]');

        if ($spans->count() > 0 && $subject->count() === 0) {
            foreach ($spans as $group) {
                $lessons[] = array_merge($this->getLesson($group, true));
            }
        } elseif (\count($chunks) > 0 && $subject->count() > 0) {
            foreach ($chunks as $item) {
                $this->setLessonFromChunk($lessons, $item);
            }
        }

        $this->updateLessonWithMultipleClasses($current, $lessons);
        $this->setFallbackLesson($current, $lessons);

        return $lessons;
    }

    private function setLessonFromChunk(array &$lessons, string $chunk): void
    {
        $doc = new Document();
        $doc->html($chunk);

        $span = $doc->find('span[style]');
        $cell = $doc->find('.l');
        $body = $doc->find('body');

        if ($span->count() > 0) {
            $lessons[] = array_merge($this->getLesson($span->first(), true));
        } elseif ($cell->count() > 0) {
            $lessons[] = $this->getLesson($cell->first());
        } elseif ($body->count() > 0) {
            $lessons[] = $this->getLesson($body->first());
        }
    }

    private function setFallbackLesson(Element $current, array &$lessons): void
    {
        if (\count($lessons) === 0 && trim($current->text(), "\xC2\xA0\n") !== '') {
            $lessons[] = $this->getLesson($current);
        }
    }

    private function updateLessonWithMultipleClasses(Element $current, array &$lessons): void
    {
        if ($current->findXPath('./*[@class="o"]')->count() < 2) {
            return;
        }

        $lastIndex = \count($lessons) - 1;

        unset($lessons[$lastIndex]['className'], $lessons[$lastIndex]['alt']);

        /** @var Element $item */
        foreach (explode(',', $current->getOuterHtml()) as $item) {
            $doc = new Document();
            $doc->html(str_replace('<td class="l">', '', $item));
            $el = $doc->find('body')->first();
            $lessons[$lastIndex]['className'][] = [
                'name'  => trim($el->find('a.o')->text()),
                'value' => $this->getUrlValue($el->find('a.o')->first(), 'o'),
                'alt'   => trim($el->findXPath('./text()')->text()),
            ];
        }
    }

    private function getLesson(Element $cell, bool $diversion = false): array
    {
        $subject = $cell->findXPath('./*[@class="p"]');

        $lesson = [
            'teacher'      => $this->getLessonPartValues($cell->findXPath('./*[@class="n"]'), 'n'),
            'room'         => $this->getLessonPartValues($cell->findXPath('./*[@class="s"]'), 's'),
            'className'    => $this->getLessonPartValues($cell->findXPath('./*[@class="o"]'), 'o'),
            'subject'      => $subject->text(),
            'diversion'    => $diversion,
            'alt'          => trim($cell->findXPath('./text()')->text()),
            'substitution' => $cell->findXPath('./*[@class="zas"]')->text(),
        ];

        $subjects = $cell->findXPath('./*[@class="p"]');
        if ($subjects->count() > 1) {
            $textBetweenSubject = $cell->findXPath('./text()[(preceding::*[@class="p"])]');
            if (trim($textBetweenSubject->text()) !== '') {
                $lesson['subject'] = $subject->first()->text().trim($textBetweenSubject->text()).' '.$subject->end()->text();
            } else {
                unset($lesson['subject']);
                foreach ($subjects as $subject) {
                    $lesson['subject'][] = $subject->text();
                }
            }
        }

        return $lesson;
    }

    private function getLessonPartValues(NodeList $part, string $prefix): array
    {
        return [
            'name'  => $part->text(),
            'value' => $this->getUrlValue($part->first(), $prefix),
        ];
    }

    private function getUrlValue(?Element $el, string $prefix): string
    {
        if (null === $el) {
            return '';
        }

        return str_replace([$prefix, '.html'], '', $el->attr('href'));
    }
}
