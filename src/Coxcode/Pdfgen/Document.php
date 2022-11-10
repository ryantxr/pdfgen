<?php
namespace Coxcode\Pdfgen;
use UnofficialFpdfOrg\FPDF;

class Document
{
    /** @var object */
    // private $object;
    /** @var Context */
    private $context;

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
        $object = json_decode($json);
        $topLevel = get_object_vars($object);
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
        
        
        $this->context = new Context($object);
        $this->context->pdf->AddPage();
        $this->context->pdf->SetAutoPageBreak(false);
        $this->context->pdf->SetRightMargin(18);
        $this->context->pdf->AliasNbPages();

        $this->drawPage();

        if (isset($document['shade']))  {
            $this->context->shade = $document['shade'];
            $color = get_object_vars($document['shade']);
            $this->context->pdf->SetFillColor($color['red'], $color['green'], $color['blue']);
            $fill = true;
        }
        else  {
            $this->context->shade = null;
            $fill = false;
        }

        foreach ($boxDefs as $key => $value) {
            $box = get_object_vars($value);
            if (isset($box['font']))  {
                if ( is_string($box['font']) ) {
                    $box['font'] = get_object_vars($this->ref($box['font']));
                } else {
                    $box['font'] = get_object_vars($box['font']);
                }
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
            $this->boxes[$box['name']] = new TextBox($this->context, $box, $fill);
        }
        if ( ! empty($imageBoxDefs) ) {
            foreach ($imageBoxDefs as $key => $value) {
                $box = get_object_vars($value);
                $this->imageBoxes[$box['name']] = new ImageBox($this->context, $box, $fill);
            }
        } else {
            $this->imageBoxes = [];
        }
        if (isset($tableDefs)) {
            foreach ($tableDefs as $tableName => $table)  {
                $this->tables[$tableName] = new Table();
                $this->tables[$tableName]->dblSpace = 6;
                foreach ($table as $value) {
                    $box = get_object_vars($value);
                    if ( isset($box['font']) )  {
                        if ( is_string($box['font']) ) {
                            $ref = $this->ref($box['font']);
                            $box['font'] = get_object_vars($ref);
                        } else {
                            $box['font'] = get_object_vars($box['font']);
                        }
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
                    
                    $this->tables[$tableName]->addBox($box['name'], new TextBox($this->context, $box, $fill));
                    if (isset($box['dblSpace']))  {
                        $this->tables[$tableName]->dblSpace = $box['dblSpace'];
                    }
                }
            }
        }

        $this->context->pdf->SetFont($this->defaultFont['name'], $this->defaultFont['style'], $this->defaultFont['size']);
        $this->blankPdf = clone $this->context->pdf;
    }

    /**
     * $fonts.helv_bold_9 => $this->fonts->helv_bold_9
     * @return object
     */
    protected function ref(string $ref) : ?object
    {
        $s = substr($ref, 1); // skip over the $
        $a = explode('.', $s);
        $p = null;
        foreach ( $a as $k ) {
            if ( $p == null ) {
                if ( ! isset($this->context->object->{$k}) ) {
                    throw new \Exception("Reference not found {$ref}");
                }
                $p = $this->context->object->{$k};
            } else {
                if ( ! isset($p->{$k}) ) {
                    throw new \Exception("Reference not found {$ref}");
                }
                $p = $p->{$k};
            }
        }
        return $p;
    }

    /**
     * Clear the document
     */
    public function clear()  {
        $newcontext = $this->createContext(clone $this->blankPdf);
        $newcontext->shade = $this->context->shade;
        $newcontext->object = $this->context->object;
        $this->context = $newcontext;

        foreach ($this->tables as $table)  {
            $table->clear($this->context);
        }

        foreach ($this->boxes as $box) {
            $box->clear($this->context);
        }
        
        foreach ($this->imageBoxes as $box) {
            $box->clear($this->context);
        }
    }

    private function createContext(?FPDF $pdf=null) : object
    {
        return (object)['pdf' => $pdf, 'shade' => null];
    }

    /**
     * Draws the page
     */
    public function drawPage()  {

        foreach ($this->images as $key => $value) {
            $image = get_object_vars($value);
            $this->context->pdf->image($image['file'], $image['x'], $image['y'], $image['xConstraint'], $image['yConstraint']);
        }

        foreach ($this->labels as $key => $value) {
            $label = get_object_vars($value);
            $text = $label['value'];
            if (isset($label['replace']))  {
                // All special variables
                $text = str_replace('{pageNo}', $this->context->pdf->PageNo(), $text);
            }
            if (isset($label['font']))  {
                if ( is_string($label['font']) ) {
                    // See if it is a reference
                    $font = $this->ref($label['font']);
                    $name = $font->name ?: $this->defaultFont['name'];
                    $size = $font->size ?: $this->defaultFont['size'];
                    $style = $font->style ?: $this->defaultFont['style'];
                } else {
                    $font = get_object_vars($label['font']);
                    $name = isset($font['name']) ? $font['name'] : $this->defaultFont['name'];
                    $size = isset($font['size']) ? $font['size'] : $this->defaultFont['size'];
                    $style = isset($font['style']) ? $font['style'] : $this->defaultFont['style'];
                    if (isset($font['color']))  {
                        $color = get_object_vars($font['color']);
                        $this->context->pdf->SetTextColor($color['red'], $color['green'], $color['blue']);
                    }
                }
            } else {
                $name = $this->defaultFont['name'];
                $size = $this->defaultFont['size'];
                $style = $this->defaultFont['style'];
            }
            $this->context->pdf->SetFont($name, $style, $size);
            $this->context->pdf->SetXY($label['x'], $label['y']);
            $this->context->pdf->write($size, $text);
            $this->context->pdf->SetTextColor(0,0,0);
        }

        foreach ($this->lines as $key => $value) {
            $line = get_object_vars($value);
            $this->context->pdf->SetLineWidth(isset($line['lineWidth']) ? $line['lineWidth'] : $this->lineWidth);
            
            // $this->context->pdf->SetDrawColor(127, 127, 159);
            if (isset($line['hdash']))  {
                $x = $line['startX']; 
                while ($x < $line['endX'])  {
                    if ($x + $line['hdash'] > $line['endX'])  {
                        $stopX = $line['endX'];
                    }  
                    else  {
                        $stopX = $x + $line['hdash'];
                    }
                    $this->context->pdf->Line($x, $line['startY'], $stopX, $line['endY']);
                    $x += 2*$line['hdash'];
                }
            }
            else {
                $this->context->pdf->Line($line['startX'], $line['startY'], $line['endX'], $line['endY']);
            }
            // $this->popDrawColor();
        }
    }

    /**
     * Set the text for a text box by name
     */
    public function setText(string $boxName, string $text, $color = null, $justification = null, $font = null)  {
        (new Log)->debug(__METHOD__ . " {$boxName}");
        if (isset($this->boxes[$boxName]))  {
            $box = $this->boxes[$boxName];
            $box->setText(explode("\n", $text), $justification, $font, $color);
        }		
        else  {
            if (isset($this->tables))  {
                (new Log)->debug("We have tables");
                // (new Log)->debug("tables: " . print_r($this->tables, true));
                foreach ($this->tables as $table) {
                    (new Log)->debug("table boxes: " . print_r($table->boxes, true));
                    if (isset($table->boxes[$boxName])) {
                        (new Log)->debug("Found table box to update");
                        
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
                $this->context->pdf->AddPage();
                $this->drawPage();
            }
        }
        $this->context->pdf->Output($dest, $file);
    }
}