<?php
namespace Coxcode\Pdfgen;

// Need fpdf to have been included

class Row  {

	/** @var Context */
    public $context ;
    public $text ;
    public $font ;
    public $linesPrinted ;
    public $color ;
    public $lines ;
    public $justification ;


	public function __construct(Context $context, $text, $width, $font, $shrink = true, $justification = null, $color = null)  {
		$this->context = $context;
		$this->text = $text;
		$this->font = $font;
		$this->linesPrinted = 0;

		// These are to allow finer control of color and justification and are used when writing
		$this->justification = $justification;		
		if ($color != null)  {
			$this->color = $color;
		}

		if ($shrink)  {
			$this->lines = $text;
			// This will force the font to shrink until width fits
			$this->forceFit($text, $width);
		} else {
			$this->lines = $this->wrapWords($text, $width);
		}
	}

	public function forceFit($text, $width)  {
		$this->context->pdf->SetFont($this->font['name'], $this->font['style'], $this->font['size']);
		// This only makes sense for one line rows but we should not crash if there are multiples

		foreach ($text as $line) {
			$length = $this->context->pdf->GetStringWidth($line);
			while ($length >= $width)  {
				$this->font['size'] -= 0.5;
				$this->context->pdf->SetFont($this->font['name'], $this->font['style'], $this->font['size']);
				$length = $this->context->pdf->GetStringWidth($line);
			}
		}
	}

	public function wrapWords($text, $width)  {
		$this->context->pdf->SetFont($this->font['name'], $this->font['style'], $this->font['size']);

		$wrapped = [];
		foreach ($text as $line) {
			$length = $this->context->pdf->GetStringWidth($line);
			if ($length >= $width)  {
				$wrapped = array_merge($wrapped, $this->doWrap($line, $width));
			} else  {
				$wrapped[] = $line;
			}
		}

		return $wrapped;
	}

	private function doWrap($line, $width)  {
		$words = explode(' ', $line);

		$x = $this->context->pdf->GetStringWidth($words[0]);
		$wrapped[0] = $words[0];
		$lines=0;

		for ($word = 1; $word < count($words); $word++)  {
			$nextX = $this->context->pdf->GetStringWidth(' ' . $words[$word]);
			if (($x + $nextX) < $width)  {
				$wrapped[$lines] .= ' ' . $words[$word];
				$x += $nextX;
				continue;
			}

			$x = $this->context->pdf->GetStringWidth($words[$word]);
			$wrapped[++$lines] = $words[$word];
		}
		return $wrapped;
	}
}