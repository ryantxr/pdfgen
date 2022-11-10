<?php
namespace Coxcode\Pdfgen;

class Table  {
	public $numRows = 0;
	private $sizeLines = 100000;  // Just using a large value
    public $boxes;
    public $rows;
    public $lines;
    private $whichPage;
    public $dblSpace;

    /**
     * 
     */
	public function addBox(string $boxName, TextBox $box)  {
		$this->boxes[$boxName] = $box;
		$sizeLines = $box->getLines();
		if ( $this->sizeLines > $sizeLines )  {
			$this->sizeLines = $sizeLines;
		}

	}

	/**
     * Making a font size bigger would be a big problem
     */
	public function setText(string $boxName, $text, $justification = null, $font = null, $color = null)  {
        (new Log)->debug(__METHOD__);
		$broken = explode("\n\n", $text);

		$box = $this->boxes[$boxName];

		if ($font == null)  {
			$font = $box->font;
		}

		foreach ($broken as $row) {
			$this->rows[$boxName][] = new Row($box->getContext(), explode("\n", $row), $box->sizeX, $font, false, $justification, $color);
			// Tracking total number of rows to keep rows lined up
			if (count($this->rows[$boxName]) > $this->numRows)  {
				$this->numRows++;
			}
		}
	}

    /**
     * 
     */
	public function clear($context)  {
		unset($this->rows);
		$this->numRows = 0;
		foreach ($this->boxes as $box)  {
			$box->clear($context);
		}
	}

    /**
     * 
     */
	public function neededPages()  {
		$numPages = 1;
		$usedLines = 0;

		for( $i = 0; $i < $this->numRows; $i++ )  {
			$maxLines = 0;
			foreach ( $this->boxes as $name => $box ) {
				if ( empty($this->rows[$name][$i]) )  {
					continue;
				}

				// Get specific row object for checking
				$row = $this->rows[$name][$i];
				$rowLines = count($row->lines);
				if ( $rowLines > $maxLines )  {
					$maxLines = $rowLines;
				}
			}

			$this->lines[$i] = $maxLines;
			$thisPage = $numPages;
			if ( ($maxLines + $usedLines) > $this->sizeLines )  {
				// Wrap to next page
				$thisPage++;
				$numPages += ceil($maxLines / $this->sizeLines);
				$usedLines = $maxLines % $this->sizeLines;
				// If perfect fit
				if ( $usedLines == 0 )  {
					$usedLines = $maxLines;
				}
			}
			$this->whichPage[$i] = $thisPage;

		}
		return $numPages;
	}

    /**
     * 
     */
	public function finalize($page, $total)  {
		foreach ($this->boxes as $name => $box) {
			$box->newPage();
		}

		for( $i = 0; $i < $this->numRows; $i++ )  {
			if ( $this->whichPage[$i] != $page )  {
				continue;
			}

			$maxDeltY = 0;
			$multiPage = false;
			foreach ( $this->boxes as $name => $box ) {
				if ( !empty($this->rows[$name][$i]->lines))  {
					$remainingLines = $box->write([ $this->rows[$name][$i] ], $this->lines[$i], true);


					if ( $remainingLines )  {
						$multiPage = true;
					}

					if ($box->deltY > $maxDeltY)  {
						$maxDeltY = $box->deltY;
					}
 				}
			}

			// Force double space
			$maxDeltY += $this->dblSpace;
			foreach ($this->boxes as $box) {
				if ($box->deltY < $maxDeltY)  {
					$box->deltY = $maxDeltY;
				}
			}

			if ($multiPage)  {
				$this->whichPage[$i]++;
				$i--;
			}
		}
	}
}