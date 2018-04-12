<?php

namespace Wulkanowy\TimetableParser;

use DOMWrap\Document;
use DOMWrap\Element;

class TimetableList
{
    private $doc;

    public function __construct(string $html)
    {
        $this->doc = new Document();
        $this->doc->html($html);
    }

    public function getListUrl()
    {
        return $this->doc->find('frame[name=list]')->attr('src');
    }

    public function getTimetableList(): array
    {
        if ($this->doc->find('form[name=form]')->count() > 0) {
            return $this->getTimetableSelectListType();
        }

        if ($this->doc->find('body table')->count() > 0) {
            return $this->getTimetableExpandableListType();
        }

        return $this->getTimetableUnorderedListType();
    }

    private function getTimetableSelectListType(): array
    {
        return [
            'classes'  => $this->getSelectListValues('oddzialy'),
            'teachers' => $this->getSelectListValues('nauczyciele'),
            'rooms'    => $this->getSelectListValues('sale'),
        ];
    }

    private function getSelectListValues($name): array
    {
        $nodes = $this->doc->find('[name='.$name.'] option');
        $nodes->shift();
        $values = [];

        /** @var Element $class */
        foreach ($nodes as $class) {
            $values[] = [
                'name'  => $class->text(),
                'value' => $class->attr('value'),
            ];
        }

        return $values;
    }

    private function getTimetableUnorderedListType(): array
    {
        $teacherQ = 'ul:nth-of-type(2) a';
        $roomsQ = 'ul:nth-of-type(3) a';

        if ($this->doc->find('h4')->count() === 1) {
            $teacherQ = 'undefined';
            $roomsQ = 'undefined';
        } elseif ($this->doc->find('h4:nth-of-type(2)')->text() === 'Sale') {
            $teacherQ = 'undefined';
            $roomsQ = 'ul:nth-of-type(2) a';
        }

        return $this->getTimetableUrlSubType(
            'ul:first-of-type a',
            $teacherQ,
            $roomsQ
        );
    }

    private function getTimetableExpandableListType(): array
    {
        return $this->getTimetableUrlSubType(
            '#oddzialy a',
            '#nauczyciele a',
            '#sale a'
        );
    }

    private function getTimetableUrlSubType(string $classQ, string $teachersQ, string $roomsQ): array
    {
        return [
            'classes'  => $this->getSubTypeValue($classQ, 'o'),
            'teachers' => $this->getSubTypeValue($teachersQ, 'n'),
            'rooms'    => $this->getSubTypeValue($roomsQ, 's'),
        ];
    }

    private function getSubTypeValue(string $query, string $prefix): array
    {
        $values = [];

        /* @var Element $class */
        foreach ($this->doc->find($query) as $item) {
            $values[] = [
                'name'  => $item->text(),
                'value' => str_replace(
                    'plany/'.$prefix,
                    '',
                    str_replace('.html', '', $item->attr('href'))
                ),
            ];
        }

        return $values;
    }
}
