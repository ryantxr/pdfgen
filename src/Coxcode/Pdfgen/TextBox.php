<?php
namespace Coxcode\Pdfgen;

use UnofficialFpdfOrg\FPDF;

// Need fpdf to have been included

class TextBox  {
    /** @var Context */
    public $context;
    /** @var int */
    public $x;
    /** @var int */
    public $y;
    /** @var int */
    public $sizeX;
    /** @var int */
    public $sizeY;
    public $font;
    public $rows;
    public $justification ;
    public $color ;
    public $page ;
    public $shade ;
    public $deltX ;
    public $deltY ;
    public $maxDeltX ;
    public $maxDeltY ;

	public function __construct(Context $context, $params, ?bool $shade = false)  {
		$this->context = $context;
		$this->x = $params['x'];
		$this->y = $params['y'];
		$this->sizeX = $params['sizeX'] ?? 0;
		$this->sizeY = $params['sizeY'] ?? 0;
		$this->font = $params['font'];
		$this->rows = null;

		$this->page = isset($params['page']) ? $params['page'] : 'all';
		$this->justification = isset($params['justification']) ? $params['justification'] : 'L';

		$this->shade = $shade;

		$this->deltX = 0;
		$this->deltY = 0;
		$this->maxDeltY = 0;
	}

	/**
	 * @return FPDF
	 */
	public function pdf() : FPDF
	{
		return $this->context->pdf;
	}

	/**
	 * return the context
	 * @return Context
	 */
	public function getContext() : Context
	{
		return $this->context;
	}

	public function setColor($color)  {
		$this->color = $color;
	}
	
	//public function write($text, $newLine = true, $justification = 'L', $fontOverride = null)  {
	public function write($rows = null, $lineOverride = 0, $pageable = false)  {
		if ( $rows == null )  {
			$rows = $this->rows;
		}
		if ( empty($rows) )  {
			// Nothing to do
			return;
		}
        $multiPage = null;
		foreach ( $rows as $key => $row ) {
			$this->context->pdf->SetFont($row->font['name'], $row->font['style'], $row->font['size']);
			$notBlack = false;

			$color = null;
			if (isset($row->color))  {
				$color = $row->color;
			}
			else if (isset($row->font['color']))  {
				$color = get_object_vars($row->font['color']);
			}

			if ($color)  {
				$this->context->pdf->SetTextColor($color['red'], $color['green'], $color['blue']);
				$notBlack = true;
			}

			$fontSize = $row->font['size'];
			if ($this->maxDeltY < $fontSize)  {  // fixed font at the moment
				$this->maxDeltY = $fontSize;
			}

			$numLines = count($row->lines) - $row->linesPrinted;
			$startLine = $row->linesPrinted;
			$needY =  $numLines*$fontSize + ($numLines-1) * 2;
			$haveY = $this->sizeY - $this->deltY;
			$multiPage = false;
			if ( $needY > $haveY )  {
				// We have an error
				if ( $pageable ) {
					$numLines = $this->getLines();
					$startLine = $row->linesPrinted;
					$row->linesPrinted += $numLines;
					$multiPage = true;
				} else {
					$row->lines = [ 'error' ];
				}
			}

			$skippedLines = 0;
			$linesPrinted = 0;
			foreach ( $row->lines as $line )  {
				if ( empty($line) )  {
					continue;
				}

				if ( $skippedLines < $startLine )  {
					$skippedLines++;
					continue;
				}

				if ( $linesPrinted >= $numLines )  {
					break;
				}
				$linesPrinted++;

				$strLength = $this->context->pdf->GetStringWidth($line);
				if ($strLength >= $this->sizeX)  {
                	// We have an error
                	$line = 'error';
            	}

            	$justification = ($row->justification == null) ? $this->justification : $row->justification;

				switch ($justification) {
					default:
					case 'L':
						// The left needs nothing
						break;
			
					case 'C':
						$this->deltX = ($this->sizeX - $strLength - 4)/2;
						break;

					case 'R':
						$this->deltX = $this->sizeX - $strLength;
						break;

				}

				$this->context->pdf->SetXY($this->x + $this->deltX, $this->y + $this->deltY);
				$this->context->pdf->write($fontSize, $line);

				$this->deltX = 0;
				$this->deltY += $this->maxDeltY + 2;
			}

			// Need to fix additional lines
			if ( $lineOverride > 0 )  {
				$this->deltY += ($lineOverride - $numLines) * ($this->maxDeltY + 2);
			}

			if ( $notBlack )  {
				$this->context->pdf->SetTextColor(0, 0, 0);
			}
		}

		return $multiPage;  // return false if field won't fit
	}

	public function setText($text, $justification = null, $useFont = null, $color = null)  {
		if ($useFont == null)  {
			$useFont = $this->font;
		}

		$row = new Row($this->context, $text, $this->sizeX, $useFont, true, $justification, $color);
		$this->rows[] = $row;

	}

	public function clear($context)  {
		$this->rows = null;
		//  Why did I do this?
		// $this->context = $context;
	}

    /**
     * 
     */
	public function getLines() : int {
		$fontSize = (int)$this->font['size'];

		return (int) ( ( (int)($this->sizeY) + 2 ) / ( (int)$fontSize + 2 ) );
	}

	public function newPage()  {
		if ($this->shade)  {
			
			$this->context->pdf->SetXY($this->x + 2, $this->y);
			$this->context->pdf->Cell($this->sizeX, $this->sizeY, '', 0, 0, '', true);
		}		
		$this->maxDeltY = 0;
		$this->deltX = $this->deltY = 0;
	}

	public function finalize($page = 1, $total = 1)  {
		$this->newPage();

		if (isset($this->rows))  {
			// If pageable, then we do lines manually

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
				$this->write();
			}
		}
	}
}