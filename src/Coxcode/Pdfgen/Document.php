<?php
namespace Coxcode\Pdfgen;
use UnofficialFpdfOrg\FPDF;

class Document
{
    private $images;
    private $labels;
    private $lines;
    private $defaultFont;
    private $lineWidth;

    private $pdf;
    private $boxes;
    private $imageBoxes;
    /** @var array<Table> */
    public $tables;
    public $dblSpace;
    private $blankPdf;
    // private $;


    /**
     * Constructor
     */
    public function __construct($json)  {
		$decoded = json_decode($json);
		$topLevel = get_object_vars($decoded);
		$document = get_object_vars($topLevel['doc']);
		$this->images = $topLevel['images'];
		$this->labels = $topLevel['labels'];
		$this->lines = $topLevel['lines'];
		$boxDefs = $topLevel['boxes'];
		$imageBoxDefs = $topLevel['imageBoxes'];
		if (isset($topLevel['tables']))  {
			$tableDefs = get_object_vars($topLevel['tables']);
		}
		$this->defaultFont = get_object_vars($document['defaultFont']);
		if (isset($document['lineWidth']))  {
			$this->lineWidth = $document['lineWidth'];
		}
		else  {
			$this->lineWidth = 0.25;
		}


		$this->pdf = new FPDF($document['orientation'], $document['unit'], $document['size']);
		$this->pdf->AddPage();
		$this->pdf->SetAutoPageBreak(false);
		$this->pdf->SetRightMargin(18);
		$this->pdf->AliasNbPages();

		$this->drawPage();

		if (isset($document['shade']))  {
			$color = get_object_vars($document['shade']);
			$this->pdf->SetFillColor($color['r'], $color['g'], $color['b']);
			$fill = true;
		}
		else  {
			$fill = false;
		}

		foreach ($boxDefs as $key => $value) {
			$box = get_object_vars($value);
			if (isset($box['font']))  {
				$box['font'] = get_object_vars($box['font']);
				if (! isset($box['font']['style']))  {
					$box['font']['style'] = $this->defaultFont['style'];
				}
				if (! isset($box['font']['name']))  {
					$box['font']['name'] = $this->defaultFont['name'];
				}
				if (! isset($box['font']['size']))  {
					$box['font']['size'] =  $this->defaultFont['size'];
				}
			} else  {
				$box['font'] = $this->defaultFont;
			}
			$this->boxes[$box['name']] = new TextBox($this->pdf, $box, $fill);
		}
		foreach ($imageBoxDefs as $key => $value) {
			$box = get_object_vars($value);
			$this->imageBoxes[$box['name']] = new ImageBox($this->pdf, $box, $fill);
		}
		if (isset($tableDefs)) {
			foreach ($tableDefs as $tableName => $table)  {
				$this->tables[$tableName] = new Table();
				$this->tables[$tableName]->dblSpace = 6;
				foreach ($table as $value) {
					$box = get_object_vars($value);
					if (isset($box['font']))  {
						$box['font'] = get_object_vars($box['font']);
						if (! isset($box['font']['style']))  {
							$box['font']['style'] = $this->defaultFont['style'];
						}
						if (! isset($box['font']['name']))  {
							$box['font']['name'] = $this->defaultFont['name'];
						}
						if (! isset($box['font']['size']))  {
							$box['font']['size'] =  $this->defaultFont['size'];
						}
					} else {
						$box['font'] = $this->defaultFont;
					}
					
					$this->tables[$tableName]->addBox($box['name'], new TextBox($this->pdf, $box, $fill));
					if (isset($box['dblSpace']))  {
						$this->tables[$tableName]->dblSpace = $box['dblSpace'];
					}
				}
			}
		}

		$this->pdf->SetFont($this->defaultFont['name'], $this->defaultFont['style'], $this->defaultFont['size']);
		$this->blankPdf = clone $this->pdf;
	}

    /**
     * Clear the document
     */
	public function clear()  {
		$this->pdf = clone $this->blankPdf;

		foreach ($this->tables as $table)  {
			$table->clear($this->pdf);
		}

		foreach ($this->boxes as $box) {
			$box->clear($this->pdf);
		}
		foreach ($this->imageBoxes as $box) {
			$box->clear($this->pdf);
		}

	}

    /**
     * Draws the page
     */
	public function drawPage()  {

		foreach ($this->images as $key => $value) {
			$image = get_object_vars($value);
			$this->pdf->image($image['file'], $image['x'], $image['y'], $image['xConstraint'], $image['yConstraint']);
		}

		foreach ($this->labels as $key => $value) {
			$label = get_object_vars($value);
			$text = $label['value'];
			if (isset($label['replace']))  {
				// All special variables
				$text = str_replace('{pageNo}',$this->pdf->PageNo(),$text);
			}
			if (isset($label['font']))  {
				$font = get_object_vars($label['font']);
				$name = isset($font['name']) ? $font['name'] : $this->defaultFont['name'];
				$size = isset($font['size']) ? $font['size'] : $this->defaultFont['size'];
				$style = isset($font['style']) ? $font['style'] : $this->defaultFont['style'];
				if (isset($font['color']))  {
					$color = get_object_vars($font['color']);
					$this->pdf->SetTextColor($color['red'], $color['green'], $color['blue']);
				}
			}
			else {
				$name = $this->defaultFont['name'];
				$size = $this->defaultFont['size'];
				$style = $this->defaultFont['style'];
			}
			$this->pdf->SetFont($name, $style, $size);
			$this->pdf->SetXY($label['x'], $label['y']);
			$this->pdf->write($size, $text);
			$this->pdf->SetTextColor(0,0,0);
		}

		foreach ($this->lines as $key => $value) {
			$line = get_object_vars($value);
			$this->pdf->SetLineWidth(isset($line['lineWidth']) ? $line['lineWidth'] : $this->lineWidth);

			if (isset($line['hdash']))  {
				$x = $line['startX']; 
				while ($x < $line['endX'])  {
					if ($x + $line['hdash'] > $line['endX'])  {
						$stopX = $line['endX'];
					}  
					else  {
						$stopX = $x + $line['hdash'];
					}
					$this->pdf->Line($x, $line['startY'], $stopX, $line['endY']);
					$x += 2*$line['hdash'];
				}
			}
			else {
				$this->pdf->Line($line['startX'], $line['startY'], $line['endX'], $line['endY']);
			}
		}
	}

    /**
     * Set the text for a text box by name
     */
	public function setText(string $boxName, string $text, $color = null, $justification = null, $font = null)  {
		if (isset($this->boxes[$boxName]))  {
			$box = $this->boxes[$boxName];
			$box->setText(explode("\n", $text), $justification, $font, $color);
		}		
		else  {
			if (isset($this->tables))  {
				foreach ($this->tables as $table) {
					if (isset($table->boxes[$boxName]))  {
						$table->setText($boxName, $text, $justification, $font, $color);
						break;
					}
				}
			}
		}
	}

    /**
     * 
     */
	public function setImage(string $boxName, $image)  {
		if (isset($this->imageBoxes[$boxName]))  {
			$box = $this->imageBoxes[$boxName];
			$box->setImage($image);
		}
	}

    /**
     * 
     */
	public function outputBrowser(string $fileName = 'doc.pdf')  {
		$this->Output('I');
	}

    /**
     * 
     */
	public function outputFile(string $fileName)  {
		$this->Output('F', $fileName);
	}

    /**
     * 
     */
	public function Output(string $dest, $file='doc.pdf')  {
		// Calculate Pages
		$numPages = 1;

		foreach ($this->tables as $table) {
			$neededPages = $table->neededPages();
			if ($neededPages > $numPages)  {
				$numPages = $neededPages;
			}
		}

		$thisPage = 1; 
		while ($thisPage <= $numPages)  {

			foreach ($this->tables as $table)  {
				$table->finalize($thisPage, $numPages);
			}

			foreach ($this->boxes as $box) {
				$box->finalize($thisPage, $numPages);
			}
			foreach ($this->imageBoxes as $box) {
				$box->finalize($thisPage, $numPages);
			}
			if (++$thisPage <= $numPages)  {
				$this->pdf->AddPage();
				$this->drawPage();
			}
		}
		$this->pdf->Output($dest, $file);
	}
}