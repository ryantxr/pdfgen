<?php
namespace Coxcode\Pdfgen;
use UnofficialFpdfOrg\FPDF;

class Context
{
    /** @var FPDF */
    public $pdf;

    /** @var \StdClass */
    public $object;

    protected $currentDrawColor;
    protected $drawColorStack = [];

    public function __construct($object)
    {
        $this->object = $object;
        $this->pdf = new FPDF($object->doc->orientation, $object->doc->unit, $object->doc->size);
    }

    /**
     * description
     * 
     * @return void
     */
    public function pushDrawColor()
    {
        $this->drawColorStack[] = $this->currentDrawColor;
    }

    /**
     * pop the draw color from the stack
     * @return void
     */
    public function popDrawColor()
    {
        if ( empty($this->drawColorStack) ) {
            return;
        }
        $a = array_pop($this->drawColorStack);
        $this->currentDrawColor = $a;
        return $a;
    }
}