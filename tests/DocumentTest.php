<?php declare(strict_types=1);


use PHPUnit\Framework\TestCase;


class DocumentTest extends TestCase
{
    /**
     * @test
     * Make sure the installation is ok.
     */
    public function smoke()
    {
        $json = file_get_contents(__DIR__ . '/document_test_1.json');
        $doc = new \Coxcode\Pdfgen\Document($json);
        $this->assertTrue(is_object($doc));
    }   
}