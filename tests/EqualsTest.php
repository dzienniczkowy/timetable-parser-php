<?php

namespace Wulkanowy\Tests\TimetableParser;

use DOMWrap\Document;
use Mihaeu\HtmlFormatter;
use PHPUnit\Framework\TestCase;
use Wulkanowy\TimetableParser\Table;

class EqualsTest extends TestCase
{
    public function testClass(): void
    {
        $remote = file_get_contents(__DIR__.'/fixtures/oddzial.html');
        $generated = $this->getGeneratedHTML($remote);

        $this->assertEquals($this->format($remote), $this->format($generated));
    }

    public function testRoom(): void
    {
        $remote = file_get_contents(__DIR__.'/fixtures/sala.html');
        $actual = $this->getGeneratedHTML($remote);

        $this->assertEquals($this->format($remote), $this->format($actual));
    }

    private function getGeneratedHTML(string $html)
    {
        $table = (new Table($html))->getTable();

        ob_start();
        require __DIR__.'/template.html.php';
        $rendered = ob_get_contents();
        ob_end_clean();

        return $rendered;
    }

    private function format($html): string
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        @$dom->loadHTML($html);

        $html = str_replace([PHP_EOL.' '.PHP_EOL], PHP_EOL, HtmlFormatter::format($dom->saveHTML(), ''));

        $doc = new Document();
        $doc->html($html);
        $doc->find('.tabela');

        return $doc->find('.tabela')->first()->getHtml();
    }
}
