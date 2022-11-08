<?php
namespace Coxcode\Pdfgen;

// Need fpdf to have been included

class ImageBox  {

    public $pdf;
    public $x;
    public $y;
    public $xConstraint;
    public $yConstraint;
    public $page;
    public $image;
    public $shade;


	public function __construct($pdf, $params, $shade = null)  {
		$this->pdf = $pdf;
		$this->x = $params['x'];
		$this->y = $params['y'];
		$this->xConstraint = $params['xConstraint'];
		$this->yConstraint = $params['yConstraint'];
		$this->page = isset($params['page']) ? $params['page'] : 'all';
        $this->shade = $shade;
	}

	public function write($image)  {
		$this->pdf->SetXY($this->x, $this->y);
		$this->pdf->image($image, $this->x, $this->y, $this->xConstraint, $this->yConstraint);

		return true;  // return false if field won't fit
	}

	public function setImage($image)  {
		$this->image = $image;
	}

	public function clear($pdf)  {
		unset($this->image);
		$this->pdf = $pdf;
	}

	public function newPage()  {
		// No Cleanup now
	}

	public function finalize($page = 1, $total = 1)  {
		if (! isset($this->image))  {
			return;
		}

		$this->newPage();

		$doPrint = true;
		switch ($this->page)  {
			case 'first'  :  {
				if ($page != 1)  {
					$doPrint = false;
				}
				break;
			}
			case 'last' : {
				if ($page != $total)  {
					$doPrint = false;
				}
				break;
			}
		}
		if ($doPrint)  {
			$this->write($this->image);
		}
	}
}