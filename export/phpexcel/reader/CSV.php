<?php
/**
 * Generate_PHPExcel
 *
 * Copyright (c) 2006 - 2014 Generate_PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   Generate_PHPExcel
 * @package    Generate_PHPExcel_Reader
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.8.0, 2014-03-02
 */


/** Generate_PHPExcel root directory */
if (!defined('Generate_PHPExcel_ROOT')) {
	/**
	 * @ignore
	 */
	define('Generate_PHPExcel_ROOT', dirname(__FILE__) . '/../../');
	require(Generate_PHPExcel_ROOT . 'Generate_PHPExcel/Autoloader.php');
}

/**
 * Generate_PHPExcel_Reader_CSV
 *
 * @category   Generate_PHPExcel
 * @package    Generate_PHPExcel_Reader
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
class Generate_PHPExcel_Reader_CSV extends Generate_PHPExcel_Reader_Abstract implements Generate_PHPExcel_Reader_IReader
{
	/**
	 * Input encoding
	 *
	 * @access	private
	 * @var	string
	 */
	private $_inputEncoding	= 'UTF-8';

	/**
	 * Delimiter
	 *
	 * @access	private
	 * @var string
	 */
	private $_delimiter		= ',';

	/**
	 * Enclosure
	 *
	 * @access	private
	 * @var	string
	 */
	private $_enclosure		= '"';

	/**
	 * Line ending
	 *
	 * @access	private
	 * @var	string
	 */
	private $_lineEnding	= PHP_EOL;

	/**
	 * Sheet index to read
	 *
	 * @access	private
	 * @var	int
	 */
	private $_sheetIndex	= 0;

	/**
	 * Load rows contiguously
	 *
	 * @access	private
	 * @var	int
	 */
	private $_contiguous	= false;

	/**
	 * Row counter for loading rows contiguously
	 *
	 * @var	int
	 */
	private $_contiguousRow	= -1;


	/**
	 * Create a new Generate_PHPExcel_Reader_CSV
	 */
	public function __construct() {
		$this->_readFilter		= new Generate_PHPExcel_Reader_DefaultReadFilter();
	}

	/**
	 * Validate that the current file is a CSV file
	 *
	 * @return boolean
	 */
	protected function _isValidFormat()
	{
		return TRUE;
	}

	/**
	 * Set input encoding
	 *
	 * @param string $pValue Input encoding
	 */
	public function setInputEncoding($pValue = 'UTF-8')
	{
		$this->_inputEncoding = $pValue;
		return $this;
	}

	/**
	 * Get input encoding
	 *
	 * @return string
	 */
	public function getInputEncoding()
	{
		return $this->_inputEncoding;
	}

	/**
	 * Move filepointer past any BOM marker
	 *
	 */
	protected function _skipBOM()
	{
		rewind($this->_fileHandle);

		switch ($this->_inputEncoding) {
			case 'UTF-8':
				fgets($this->_fileHandle, 4) == "\xEF\xBB\xBF" ?
					fseek($this->_fileHandle, 3) : fseek($this->_fileHandle, 0);
				break;
			case 'UTF-16LE':
				fgets($this->_fileHandle, 3) == "\xFF\xFE" ?
					fseek($this->_fileHandle, 2) : fseek($this->_fileHandle, 0);
				break;
			case 'UTF-16BE':
				fgets($this->_fileHandle, 3) == "\xFE\xFF" ?
					fseek($this->_fileHandle, 2) : fseek($this->_fileHandle, 0);
				break;
			case 'UTF-32LE':
				fgets($this->_fileHandle, 5) == "\xFF\xFE\x00\x00" ?
					fseek($this->_fileHandle, 4) : fseek($this->_fileHandle, 0);
				break;
			case 'UTF-32BE':
				fgets($this->_fileHandle, 5) == "\x00\x00\xFE\xFF" ?
					fseek($this->_fileHandle, 4) : fseek($this->_fileHandle, 0);
				break;
			default:
				break;
		}
	}

	/**
	 * Return worksheet info (Name, Last Column Letter, Last Column Index, Total Rows, Total Columns)
	 *
	 * @param 	string 		$pFilename
	 * @throws	Generate_PHPExcel_Reader_Exception
	 */
	public function listWorksheetInfo($pFilename)
	{
		// Open file
		$this->_openFile($pFilename);
		if (!$this->_isValidFormat()) {
			fclose ($this->_fileHandle);
			throw new Generate_PHPExcel_Reader_Exception($pFilename . " is an Invalid Spreadsheet file.");
		}
		$fileHandle = $this->_fileHandle;

		// Skip BOM, if any
		$this->_skipBOM();

		$escapeEnclosures = array( "\\" . $this->_enclosure, $this->_enclosure . $this->_enclosure );

		$worksheetInfo = array();
		$worksheetInfo[0]['worksheetName'] = 'Worksheet';
		$worksheetInfo[0]['lastColumnLetter'] = 'A';
		$worksheetInfo[0]['lastColumnIndex'] = 0;
		$worksheetInfo[0]['totalRows'] = 0;
		$worksheetInfo[0]['totalColumns'] = 0;

		// Loop through each line of the file in turn
		while (($rowData = fgetcsv($fileHandle, 0, $this->_delimiter, $this->_enclosure)) !== FALSE) {
			$worksheetInfo[0]['totalRows']++;
			$worksheetInfo[0]['lastColumnIndex'] = max($worksheetInfo[0]['lastColumnIndex'], count($rowData) - 1);
		}

		$worksheetInfo[0]['lastColumnLetter'] = Generate_PHPExcel_Cell::stringFromColumnIndex($worksheetInfo[0]['lastColumnIndex']);
		$worksheetInfo[0]['totalColumns'] = $worksheetInfo[0]['lastColumnIndex'] + 1;

		// Close file
		fclose($fileHandle);

		return $worksheetInfo;
	}

	/**
	 * Loads Generate_PHPExcel from file
	 *
	 * @param 	string 		$pFilename
	 * @return Generate_PHPExcel
	 * @throws Generate_PHPExcel_Reader_Exception
	 */
	public function load($pFilename)
	{
		// Create new Generate_PHPExcel
		$objGenerate_PHPExcel = new Generate_PHPExcel();

		// Load into this instance
		return $this->loadIntoExisting($pFilename, $objGenerate_PHPExcel);
	}

	/**
	 * Loads Generate_PHPExcel from file into Generate_PHPExcel instance
	 *
	 * @param 	string 		$pFilename
	 * @param	Generate_PHPExcel	$objGenerate_PHPExcel
	 * @return 	Generate_PHPExcel
	 * @throws 	Generate_PHPExcel_Reader_Exception
	 */
	public function loadIntoExisting($pFilename, Generate_PHPExcel $objGenerate_PHPExcel)
	{
		$lineEnding = ini_get('auto_detect_line_endings');
		ini_set('auto_detect_line_endings', true);

		// Open file
		$this->_openFile($pFilename);
		if (!$this->_isValidFormat()) {
			fclose ($this->_fileHandle);
			throw new Generate_PHPExcel_Reader_Exception($pFilename . " is an Invalid Spreadsheet file.");
		}
		$fileHandle = $this->_fileHandle;

		// Skip BOM, if any
		$this->_skipBOM();

		// Create new Generate_PHPExcel object
		while ($objGenerate_PHPExcel->getSheetCount() <= $this->_sheetIndex) {
			$objGenerate_PHPExcel->createSheet();
		}
		$sheet = $objGenerate_PHPExcel->setActiveSheetIndex($this->_sheetIndex);

		$escapeEnclosures = array( "\\" . $this->_enclosure,
								   $this->_enclosure . $this->_enclosure
								 );

		// Set our starting row based on whether we're in contiguous mode or not
		$currentRow = 1;
		if ($this->_contiguous) {
			$currentRow = ($this->_contiguousRow == -1) ? $sheet->getHighestRow(): $this->_contiguousRow;
		}

		// Loop through each line of the file in turn
		while (($rowData = fgetcsv($fileHandle, 0, $this->_delimiter, $this->_enclosure)) !== FALSE) {
			$columnLetter = 'A';
			foreach($rowData as $rowDatum) {
				if ($rowDatum != '' && $this->_readFilter->readCell($columnLetter, $currentRow)) {
					// Unescape enclosures
					$rowDatum = str_replace($escapeEnclosures, $this->_enclosure, $rowDatum);

					// Convert encoding if necessary
					if ($this->_inputEncoding !== 'UTF-8') {
						$rowDatum = Generate_PHPExcel_Shared_String::ConvertEncoding($rowDatum, 'UTF-8', $this->_inputEncoding);
					}

					// Set cell value
					$sheet->getCell($columnLetter . $currentRow)->setValue($rowDatum);
				}
				++$columnLetter;
			}
			++$currentRow;
		}

		// Close file
		fclose($fileHandle);

		if ($this->_contiguous) {
			$this->_contiguousRow = $currentRow;
		}

		ini_set('auto_detect_line_endings', $lineEnding);

		// Return
		return $objGenerate_PHPExcel;
	}

	/**
	 * Get delimiter
	 *
	 * @return string
	 */
	public function getDelimiter() {
		return $this->_delimiter;
	}

	/**
	 * Set delimiter
	 *
	 * @param	string	$pValue		Delimiter, defaults to ,
	 * @return	Generate_PHPExcel_Reader_CSV
	 */
	public function setDelimiter($pValue = ',') {
		$this->_delimiter = $pValue;
		return $this;
	}

	/**
	 * Get enclosure
	 *
	 * @return string
	 */
	public function getEnclosure() {
		return $this->_enclosure;
	}

	/**
	 * Set enclosure
	 *
	 * @param	string	$pValue		Enclosure, defaults to "
	 * @return Generate_PHPExcel_Reader_CSV
	 */
	public function setEnclosure($pValue = '"') {
		if ($pValue == '') {
			$pValue = '"';
		}
		$this->_enclosure = $pValue;
		return $this;
	}

	/**
	 * Get line ending
	 *
	 * @return string
	 */
	public function getLineEnding() {
		return $this->_lineEnding;
	}

	/**
	 * Set line ending
	 *
	 * @param	string	$pValue		Line ending, defaults to OS line ending (PHP_EOL)
	 * @return Generate_PHPExcel_Reader_CSV
	 */
	public function setLineEnding($pValue = PHP_EOL) {
		$this->_lineEnding = $pValue;
		return $this;
	}

	/**
	 * Get sheet index
	 *
	 * @return integer
	 */
	public function getSheetIndex() {
		return $this->_sheetIndex;
	}

	/**
	 * Set sheet index
	 *
	 * @param	integer		$pValue		Sheet index
	 * @return Generate_PHPExcel_Reader_CSV
	 */
	public function setSheetIndex($pValue = 0) {
		$this->_sheetIndex = $pValue;
		return $this;
	}

	/**
	 * Set Contiguous
	 *
	 * @param boolean $contiguous
	 */
	public function setContiguous($contiguous = FALSE)
	{
		$this->_contiguous = (bool) $contiguous;
		if (!$contiguous) {
			$this->_contiguousRow = -1;
		}

		return $this;
	}

	/**
	 * Get Contiguous
	 *
	 * @return boolean
	 */
	public function getContiguous() {
		return $this->_contiguous;
	}

}
