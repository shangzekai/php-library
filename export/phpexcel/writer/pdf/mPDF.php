<?php
/**
 *  Generate_PHPExcel
 *
 *  Copyright (c) 2006 - 2014 Generate_PHPExcel
 *
 *  This library is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU Lesser General Public
 *  License as published by the Free Software Foundation; either
 *  version 2.1 of the License, or (at your option) any later version.
 *
 *  This library is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *  Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public
 *  License along with this library; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *  @category    Generate_PHPExcel
 *  @package     Generate_PHPExcel_Writer_PDF
 *  @copyright   Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 *  @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 *  @version     1.8.0, 2014-03-02
 */


/**  Require mPDF library */
$pdfRendererClassFile = Generate_PHPExcel_Settings::getPdfRendererPath() . '/mpdf.php';
if (file_exists($pdfRendererClassFile)) {
    require_once $pdfRendererClassFile;
} else {
    throw new Generate_PHPExcel_Writer_Exception('Unable to load PDF Rendering library');
}

/**
 *  Generate_PHPExcel_Writer_PDF_mPDF
 *
 *  @category    Generate_PHPExcel
 *  @package     Generate_PHPExcel_Writer_PDF
 *  @copyright   Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
class Generate_PHPExcel_Writer_PDF_mPDF extends Generate_PHPExcel_Writer_PDF_Core implements Generate_PHPExcel_Writer_IWriter
{
    /**
     *  Create a new Generate_PHPExcel_Writer_PDF
     *
     *  @param  Generate_PHPExcel  $Generate_PHPExcel  Generate_PHPExcel object
     */
    public function __construct(Generate_PHPExcel $Generate_PHPExcel)
    {
        parent::__construct($Generate_PHPExcel);
    }

    /**
     *  Save Generate_PHPExcel to file
     *
     *  @param     string     $pFilename   Name of the file to save as
     *  @throws    Generate_PHPExcel_Writer_Exception
     */
    public function save($pFilename = NULL)
    {
        $fileHandle = parent::prepareForSave($pFilename);

        //  Default PDF paper size
        $paperSize = 'LETTER';    //    Letter    (8.5 in. by 11 in.)

        //  Check for paper size and page orientation
        if (is_null($this->getSheetIndex())) {
            $orientation = ($this->_Generate_PHPExcel->getSheet(0)->getPageSetup()->getOrientation()
                == Generate_PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE)
                    ? 'L'
                    : 'P';
            $printPaperSize = $this->_Generate_PHPExcel->getSheet(0)->getPageSetup()->getPaperSize();
            $printMargins = $this->_Generate_PHPExcel->getSheet(0)->getPageMargins();
        } else {
            $orientation = ($this->_Generate_PHPExcel->getSheet($this->getSheetIndex())->getPageSetup()->getOrientation()
                == Generate_PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE)
                    ? 'L'
                    : 'P';
            $printPaperSize = $this->_Generate_PHPExcel->getSheet($this->getSheetIndex())->getPageSetup()->getPaperSize();
            $printMargins = $this->_Generate_PHPExcel->getSheet($this->getSheetIndex())->getPageMargins();
        }
        $this->setOrientation($orientation);

        //  Override Page Orientation
        if (!is_null($this->getOrientation())) {
            $orientation = ($this->getOrientation() == Generate_PHPExcel_Worksheet_PageSetup::ORIENTATION_DEFAULT)
                ? Generate_PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT
                : $this->getOrientation();
        }
        $orientation = strtoupper($orientation);

        //  Override Paper Size
        if (!is_null($this->getPaperSize())) {
            $printPaperSize = $this->getPaperSize();
        }

        if (isset(self::$_paperSizes[$printPaperSize])) {
            $paperSize = self::$_paperSizes[$printPaperSize];
        }

        //  Create PDF
        $pdf = new mpdf();
        $ortmp = $orientation;
        $pdf->_setPageSize(strtoupper($paperSize), $ortmp);
        $pdf->DefOrientation = $orientation;
        $pdf->AddPage($orientation);

        //  Document info
        $pdf->SetTitle($this->_Generate_PHPExcel->getProperties()->getTitle());
        $pdf->SetAuthor($this->_Generate_PHPExcel->getProperties()->getCreator());
        $pdf->SetSubject($this->_Generate_PHPExcel->getProperties()->getSubject());
        $pdf->SetKeywords($this->_Generate_PHPExcel->getProperties()->getKeywords());
        $pdf->SetCreator($this->_Generate_PHPExcel->getProperties()->getCreator());

        $pdf->WriteHTML(
            $this->generateHTMLHeader(FALSE) .
            $this->generateSheetData() .
            $this->generateHTMLFooter()
        );

        //  Write to file
        fwrite($fileHandle, $pdf->Output('', 'S'));

		parent::restoreStateAfterSave($fileHandle);
    }

}
