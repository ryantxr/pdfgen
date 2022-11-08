<?php declare(strict_types=1);


use PHPUnit\Framework\TestCase;


class FpdfTest extends TestCase
{
    /**
     * @test
     * Make sure the installation is ok.
     */
    public function smoke()
    {
        $pdf = new \UnofficialFpdfOrg\Fpdf;
        $this->assertTrue(is_object($pdf));
    }

    
}