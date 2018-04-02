<?php

namespace Wulkanowy\Tests\TimetableParser;

use Mihaeu\HtmlFormatter;
use PHPUnit\Framework\TestCase;
use Wulkanowy\TimetableParser\Table;

class EqualsTest extends TestCase
{
    public function testClass(): void
    {
        $expected = file_get_contents(__DIR__.'/fixtures/oddzial.html');
        $actual = $this->getGeneratedHTML(__DIR__.'/fixtures/oddzial.html');

        $this->assertEquals($this->format($expected), $this->format($actual));
    }

    public function testRoom(): void
    {
        $expected = file_get_contents(__DIR__.'/fixtures/sala.html');
        $actual = $this->getGeneratedHTML(__DIR__.'/fixtures/sala.html');

        $this->assertEquals($this->format($expected), $this->format($actual));
    }

    private function getGeneratedHTML(string $filename)
    {
        $table = (new Table(
            file_get_contents($filename)
        ))->getTable();

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
        $dom->loadHTML($html);

        return str_replace([PHP_EOL.' '.PHP_EOL], PHP_EOL, HtmlFormatter::format($dom->saveHTML(), ''));
    }
}
