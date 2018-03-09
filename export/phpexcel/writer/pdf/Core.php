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


/**
 *  Generate_PHPExcel_Writer_PDF_Core
 *
 *  @category    Generate_PHPExcel
 *  @package     Generate_PHPExcel_Writer_PDF
 *  @copyright   Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
abstract class Generate_PHPExcel_Writer_PDF_Core extends Generate_PHPExcel_Writer_HTML
{
    /**
     * Temporary storage directory
     *
     * @var string
     */
    protected $_tempDir = '';

    /**
     * Font
     *
     * @var string
     */
    protected $_font = 'freesans';

    /**
     * Orientation (Over-ride)
     *
     * @var string
     */
    protected $_orientation    = NULL;

    /**
     * Paper size (Over-ride)
     *
     * @var int
     */
    protected $_paperSize    = NULL;


    /**
     * Temporary storage for Save Array Return type
     *
     * @var string
     */
	private $_saveArrayReturnType;

    /**
     * Paper Sizes xRef List
     *
     * @var array
     */
    protected static $_paperSizes = array(
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER
            => 'LETTER',                 //    (8.5 in. by 11 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER_SMALL
            => 'LETTER',                 //    (8.5 in. by 11 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_TABLOID
            => array(792.00, 1224.00),   //    (11 in. by 17 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_LEDGER
            => array(1224.00, 792.00),   //    (17 in. by 11 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_LEGAL
            => 'LEGAL',                  //    (8.5 in. by 14 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_STATEMENT
            => array(396.00, 612.00),    //    (5.5 in. by 8.5 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_EXECUTIVE
            => 'EXECUTIVE',              //    (7.25 in. by 10.5 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3
            => 'A3',                     //    (297 mm by 420 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4
            => 'A4',                     //    (210 mm by 297 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4_SMALL
            => 'A4',                     //    (210 mm by 297 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_A5
            => 'A5',                     //    (148 mm by 210 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_B4
            => 'B4',                     //    (250 mm by 353 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_B5
            => 'B5',                     //    (176 mm by 250 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO
            => 'FOLIO',                  //    (8.5 in. by 13 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_QUARTO
            => array(609.45, 779.53),    //    (215 mm by 275 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_STANDARD_1
            => array(720.00, 1008.00),   //    (10 in. by 14 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_STANDARD_2
            => array(792.00, 1224.00),   //    (11 in. by 17 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_NOTE
            => 'LETTER',                 //    (8.5 in. by 11 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_NO9_ENVELOPE
            => array(279.00, 639.00),    //    (3.875 in. by 8.875 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_NO10_ENVELOPE
            => array(297.00, 684.00),    //    (4.125 in. by 9.5 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_NO11_ENVELOPE
            => array(324.00, 747.00),    //    (4.5 in. by 10.375 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_NO12_ENVELOPE
            => array(342.00, 792.00),    //    (4.75 in. by 11 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_NO14_ENVELOPE
            => array(360.00, 828.00),    //    (5 in. by 11.5 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_C
            => array(1224.00, 1584.00),  //    (17 in. by 22 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_D
            => array(1584.00, 2448.00),  //    (22 in. by 34 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_E
            => array(2448.00, 3168.00),  //    (34 in. by 44 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_DL_ENVELOPE
            => array(311.81, 623.62),    //    (110 mm by 220 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_C5_ENVELOPE
            => 'C5',                     //    (162 mm by 229 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_C3_ENVELOPE
            => 'C3',                     //    (324 mm by 458 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_C4_ENVELOPE
            => 'C4',                     //    (229 mm by 324 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_C6_ENVELOPE
            => 'C6',                     //    (114 mm by 162 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_C65_ENVELOPE
            => array(323.15, 649.13),    //    (114 mm by 229 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_B4_ENVELOPE
            => 'B4',                     //    (250 mm by 353 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_B5_ENVELOPE
            => 'B5',                     //    (176 mm by 250 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_B6_ENVELOPE
            => array(498.90, 354.33),    //    (176 mm by 125 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_ITALY_ENVELOPE
            => array(311.81, 651.97),    //    (110 mm by 230 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_MONARCH_ENVELOPE
            => array(279.00, 540.00),    //    (3.875 in. by 7.5 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_6_3_4_ENVELOPE
            => array(261.00, 468.00),    //    (3.625 in. by 6.5 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_US_STANDARD_FANFOLD
            => array(1071.00, 792.00),   //    (14.875 in. by 11 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_GERMAN_STANDARD_FANFOLD
            => array(612.00, 864.00),    //    (8.5 in. by 12 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_GERMAN_LEGAL_FANFOLD
            => 'FOLIO',                  //    (8.5 in. by 13 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_ISO_B4
            => 'B4',                     //    (250 mm by 353 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_JAPANESE_DOUBLE_POSTCARD
            => array(566.93, 419.53),    //    (200 mm by 148 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_STANDARD_PAPER_1
            => array(648.00, 792.00),    //    (9 in. by 11 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_STANDARD_PAPER_2
            => array(720.00, 792.00),    //    (10 in. by 11 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_STANDARD_PAPER_3
            => array(1080.00, 792.00),   //    (15 in. by 11 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_INVITE_ENVELOPE
            => array(623.62, 623.62),    //    (220 mm by 220 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER_EXTRA_PAPER
            => array(667.80, 864.00),    //    (9.275 in. by 12 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_LEGAL_EXTRA_PAPER
            => array(667.80, 1080.00),   //    (9.275 in. by 15 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_TABLOID_EXTRA_PAPER
            => array(841.68, 1296.00),   //    (11.69 in. by 18 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4_EXTRA_PAPER
            => array(668.98, 912.76),    //    (236 mm by 322 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER_TRANSVERSE_PAPER
            => array(595.80, 792.00),    //    (8.275 in. by 11 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4_TRANSVERSE_PAPER
            => 'A4',                     //    (210 mm by 297 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER_EXTRA_TRANSVERSE_PAPER
            => array(667.80, 864.00),    //    (9.275 in. by 12 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_SUPERA_SUPERA_A4_PAPER
            => array(643.46, 1009.13),   //    (227 mm by 356 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_SUPERB_SUPERB_A3_PAPER
            => array(864.57, 1380.47),   //    (305 mm by 487 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER_PLUS_PAPER
            => array(612.00, 913.68),    //    (8.5 in. by 12.69 in.)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4_PLUS_PAPER
            => array(595.28, 935.43),    //    (210 mm by 330 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_A5_TRANSVERSE_PAPER
            => 'A5',                     //    (148 mm by 210 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_JIS_B5_TRANSVERSE_PAPER
            => array(515.91, 728.50),    //    (182 mm by 257 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3_EXTRA_PAPER
            => array(912.76, 1261.42),   //    (322 mm by 445 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_A5_EXTRA_PAPER
            => array(493.23, 666.14),    //    (174 mm by 235 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_ISO_B5_EXTRA_PAPER
            => array(569.76, 782.36),    //    (201 mm by 276 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_A2_PAPER
            => 'A2',                     //    (420 mm by 594 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3_TRANSVERSE_PAPER
            => 'A3',                     //    (297 mm by 420 mm)
        Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3_EXTRA_TRANSVERSE_PAPER
            => array(912.76, 1261.42)    //    (322 mm by 445 mm)
    );

    /**
     *  Create a new Generate_PHPExcel_Writer_PDF
     *
     *  @param     Generate_PHPExcel    $Generate_PHPExcel    Generate_PHPExcel object
     */
    public function __construct(Generate_PHPExcel $Generate_PHPExcel)
    {
        parent::__construct($Generate_PHPExcel);
        $this->setUseInlineCss(TRUE);
        $this->_tempDir = Generate_PHPExcel_Shared_File::sys_get_temp_dir();
    }

    /**
     *  Get Font
     *
     *  @return string
     */
    public function getFont()
    {
        return $this->_font;
    }

    /**
     *  Set font. Examples:
     *      'arialunicid0-chinese-simplified'
     *      'arialunicid0-chinese-traditional'
     *      'arialunicid0-korean'
     *      'arialunicid0-japanese'
     *
     *  @param    string    $fontName
     */
    public function setFont($fontName)
    {
        $this->_font = $fontName;
        return $this;
    }

    /**
     *  Get Paper Size
     *
     *  @return int
     */
    public function getPaperSize()
    {
        return $this->_paperSize;
    }

    /**
     *  Set Paper Size
     *
     *  @param  string  $pValue Paper size
     *  @return Generate_PHPExcel_Writer_PDF
     */
    public function setPaperSize($pValue = Generate_PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER)
    {
        $this->_paperSize = $pValue;
        return $this;
    }

    /**
     *  Get Orientation
     *
     *  @return string
     */
    public function getOrientation()
    {
        return $this->_orientation;
    }

    /**
     *  Set Orientation
     *
     *  @param string $pValue  Page orientation
     *  @return Generate_PHPExcel_Writer_PDF
     */
    public function setOrientation($pValue = Generate_PHPExcel_Worksheet_PageSetup::ORIENTATION_DEFAULT)
    {
        $this->_orientation = $pValue;
        return $this;
    }

    /**
     *  Get temporary storage directory
     *
     *  @return string
     */
    public function getTempDir()
    {
        return $this->_tempDir;
    }

    /**
     *  Set temporary storage directory
     *
     *  @param     string        $pValue        Temporary storage directory
     *  @throws    Generate_PHPExcel_Writer_Exception    when directory does not exist
     *  @return    Generate_PHPExcel_Writer_PDF
     */
    public function setTempDir($pValue = '')
    {
        if (is_dir($pValue)) {
            $this->_tempDir = $pValue;
        } else {
            throw new Generate_PHPExcel_Writer_Exception("Directory does not exist: $pValue");
        }
        return $this;
    }

    /**
     *  Save Generate_PHPExcel to PDF file, pre-save
     *
     *  @param     string     $pFilename   Name of the file to save as
     *  @throws    Generate_PHPExcel_Writer_Exception
     */
    protected function prepareForSave($pFilename = NULL)
    {
        //  garbage collect
        $this->_Generate_PHPExcel->garbageCollect();

        $this->_saveArrayReturnType = Generate_PHPExcel_Calculation::getArrayReturnType();
        Generate_PHPExcel_Calculation::setArrayReturnType(Generate_PHPExcel_Calculation::RETURN_ARRAY_AS_VALUE);

        //  Open file
        $fileHandle = fopen($pFilename, 'w');
        if ($fileHandle === FALSE) {
            throw new Generate_PHPExcel_Writer_Exception("Could not open file $pFilename for writing.");
        }

        //  Set PDF
        $this->_isPdf = TRUE;
        //  Build CSS
        $this->buildCSS(TRUE);

        return $fileHandle;
    }

    /**
     *  Save Generate_PHPExcel to PDF file, post-save
     *
     *  @param     resource      $fileHandle
     *  @throws    Generate_PHPExcel_Writer_Exception
     */
    protected function restoreStateAfterSave($fileHandle)
    {
        //  Close file
        fclose($fileHandle);

        Generate_PHPExcel_Calculation::setArrayReturnType($this->_saveArrayReturnType);
    }

}
