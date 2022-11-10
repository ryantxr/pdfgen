<?php
namespace Coxcode\Pdfgen;

// Need fpdf to have been included

class ImageBox  {
	/** @var \StdClass */
    public $context;
    public $x;
    public $y;
    public $xConstraint;
    public $yConstraint;
    public $page;
    public $image;
    public $shade;


	public function __construct(Context $context, $params, $shade = null)  {
		$this->context = $context;
		$this->x = $params['x'];
		$this->y = $params['y'];
		$this->xConstraint = $params['xConstraint'];
		$this->yConstraint = $params['yConstraint'];
		$this->page = isset($params['page']) ? $params['page'] : 'all';
        $this->shade = $shade;
	}

	public function write($image)  {
		$this->context->pdf->SetXY($this->x, $this->y);
		$this->context->pdf->image($image, $this->x, $this->y, $this->xConstraint, $this->yConstraint);

		return true;  // return false if field won't fit
	}

	public function setImage($image)  {
		$this->image = $image;
	}

	public function clear($context)  {
		unset($this->image);
		$this->context = $context;
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