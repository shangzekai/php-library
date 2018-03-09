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
 * @package    Generate_PHPExcel_Writer_Excel2007
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.8.0, 2014-03-02
 */


/**
 * Generate_PHPExcel_Writer_Excel2007_Workbook
 *
 * @category   Generate_PHPExcel
 * @package    Generate_PHPExcel_Writer_Excel2007
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
class Generate_PHPExcel_Writer_Excel2007_Workbook extends Generate_PHPExcel_Writer_Excel2007_WriterPart
{
	/**
	 * Write workbook to XML format
	 *
	 * @param 	Generate_PHPExcel	$pGenerate_PHPExcel
	 * @param	boolean		$recalcRequired	Indicate whether formulas should be recalculated before writing
	 * @return 	string 		XML Output
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	public function writeWorkbook(Generate_PHPExcel $pGenerate_PHPExcel = null, $recalcRequired = FALSE)
	{
		// Create XML writer
		$objWriter = null;
		if ($this->getParentWriter()->getUseDiskCaching()) {
			$objWriter = new Generate_PHPExcel_Shared_XMLWriter(Generate_PHPExcel_Shared_XMLWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
		} else {
			$objWriter = new Generate_PHPExcel_Shared_XMLWriter(Generate_PHPExcel_Shared_XMLWriter::STORAGE_MEMORY);
		}

		// XML header
		$objWriter->startDocument('1.0','UTF-8','yes');

		// workbook
		$objWriter->startElement('workbook');
		$objWriter->writeAttribute('xml:space', 'preserve');
		$objWriter->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
		$objWriter->writeAttribute('xmlns:r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

			// fileVersion
			$this->_writeFileVersion($objWriter);

			// workbookPr
			$this->_writeWorkbookPr($objWriter);

			// workbookProtection
			$this->_writeWorkbookProtection($objWriter, $pGenerate_PHPExcel);

			// bookViews
			if ($this->getParentWriter()->getOffice2003Compatibility() === false) {
				$this->_writeBookViews($objWriter, $pGenerate_PHPExcel);
			}

			// sheets
			$this->_writeSheets($objWriter, $pGenerate_PHPExcel);

			// definedNames
			$this->_writeDefinedNames($objWriter, $pGenerate_PHPExcel);

			// calcPr
			$this->_writeCalcPr($objWriter,$recalcRequired);

		$objWriter->endElement();

		// Return
		return $objWriter->getData();
	}

	/**
	 * Write file version
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter $objWriter 		XML Writer
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeFileVersion(Generate_PHPExcel_Shared_XMLWriter $objWriter = null)
	{
		$objWriter->startElement('fileVersion');
		$objWriter->writeAttribute('appName', 'xl');
		$objWriter->writeAttribute('lastEdited', '4');
		$objWriter->writeAttribute('lowestEdited', '4');
		$objWriter->writeAttribute('rupBuild', '4505');
		$objWriter->endElement();
	}

	/**
	 * Write WorkbookPr
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter $objWriter 		XML Writer
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeWorkbookPr(Generate_PHPExcel_Shared_XMLWriter $objWriter = null)
	{
		$objWriter->startElement('workbookPr');

		if (Generate_PHPExcel_Shared_Date::getExcelCalendar() == Generate_PHPExcel_Shared_Date::CALENDAR_MAC_1904) {
			$objWriter->writeAttribute('date1904', '1');
		}

		$objWriter->writeAttribute('codeName', 'ThisWorkbook');

		$objWriter->endElement();
	}

	/**
	 * Write BookViews
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter 	$objWriter 		XML Writer
	 * @param 	Generate_PHPExcel					$pGenerate_PHPExcel
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeBookViews(Generate_PHPExcel_Shared_XMLWriter $objWriter = null, Generate_PHPExcel $pGenerate_PHPExcel = null)
	{
		// bookViews
		$objWriter->startElement('bookViews');

			// workbookView
			$objWriter->startElement('workbookView');

			$objWriter->writeAttribute('activeTab', $pGenerate_PHPExcel->getActiveSheetIndex());
			$objWriter->writeAttribute('autoFilterDateGrouping', '1');
			$objWriter->writeAttribute('firstSheet', '0');
			$objWriter->writeAttribute('minimized', '0');
			$objWriter->writeAttribute('showHorizontalScroll', '1');
			$objWriter->writeAttribute('showSheetTabs', '1');
			$objWriter->writeAttribute('showVerticalScroll', '1');
			$objWriter->writeAttribute('tabRatio', '600');
			$objWriter->writeAttribute('visibility', 'visible');

			$objWriter->endElement();

		$objWriter->endElement();
	}

	/**
	 * Write WorkbookProtection
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter 	$objWriter 		XML Writer
	 * @param 	Generate_PHPExcel					$pGenerate_PHPExcel
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeWorkbookProtection(Generate_PHPExcel_Shared_XMLWriter $objWriter = null, Generate_PHPExcel $pGenerate_PHPExcel = null)
	{
		if ($pGenerate_PHPExcel->getSecurity()->isSecurityEnabled()) {
			$objWriter->startElement('workbookProtection');
			$objWriter->writeAttribute('lockRevision',		($pGenerate_PHPExcel->getSecurity()->getLockRevision() ? 'true' : 'false'));
			$objWriter->writeAttribute('lockStructure', 	($pGenerate_PHPExcel->getSecurity()->getLockStructure() ? 'true' : 'false'));
			$objWriter->writeAttribute('lockWindows', 		($pGenerate_PHPExcel->getSecurity()->getLockWindows() ? 'true' : 'false'));

			if ($pGenerate_PHPExcel->getSecurity()->getRevisionsPassword() != '') {
				$objWriter->writeAttribute('revisionsPassword',	$pGenerate_PHPExcel->getSecurity()->getRevisionsPassword());
			}

			if ($pGenerate_PHPExcel->getSecurity()->getWorkbookPassword() != '') {
				$objWriter->writeAttribute('workbookPassword',	$pGenerate_PHPExcel->getSecurity()->getWorkbookPassword());
			}

			$objWriter->endElement();
		}
	}

	/**
	 * Write calcPr
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter	$objWriter		XML Writer
	 * @param	boolean						$recalcRequired	Indicate whether formulas should be recalculated before writing
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeCalcPr(Generate_PHPExcel_Shared_XMLWriter $objWriter = null, $recalcRequired = TRUE)
	{
		$objWriter->startElement('calcPr');

		//	Set the calcid to a higher value than Excel itself will use, otherwise Excel will always recalc
        //  If MS Excel does do a recalc, then users opening a file in MS Excel will be prompted to save on exit
        //     because the file has changed
		$objWriter->writeAttribute('calcId', 			'999999');
		$objWriter->writeAttribute('calcMode', 			'auto');
		//	fullCalcOnLoad isn't needed if we've recalculating for the save
		$objWriter->writeAttribute('calcCompleted', 	($recalcRequired) ? 1 : 0);
		$objWriter->writeAttribute('fullCalcOnLoad', 	($recalcRequired) ? 0 : 1);

		$objWriter->endElement();
	}

	/**
	 * Write sheets
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter 	$objWriter 		XML Writer
	 * @param 	Generate_PHPExcel					$pGenerate_PHPExcel
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeSheets(Generate_PHPExcel_Shared_XMLWriter $objWriter = null, Generate_PHPExcel $pGenerate_PHPExcel = null)
	{
		// Write sheets
		$objWriter->startElement('sheets');
		$sheetCount = $pGenerate_PHPExcel->getSheetCount();
		for ($i = 0; $i < $sheetCount; ++$i) {
			// sheet
			$this->_writeSheet(
				$objWriter,
				$pGenerate_PHPExcel->getSheet($i)->getTitle(),
				($i + 1),
				($i + 1 + 3),
				$pGenerate_PHPExcel->getSheet($i)->getSheetState()
			);
		}

		$objWriter->endElement();
	}

	/**
	 * Write sheet
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter 	$objWriter 		XML Writer
	 * @param 	string 						$pSheetname 		Sheet name
	 * @param 	int							$pSheetId	 		Sheet id
	 * @param 	int							$pRelId				Relationship ID
	 * @param   string                      $sheetState         Sheet state (visible, hidden, veryHidden)
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeSheet(Generate_PHPExcel_Shared_XMLWriter $objWriter = null, $pSheetname = '', $pSheetId = 1, $pRelId = 1, $sheetState = 'visible')
	{
		if ($pSheetname != '') {
			// Write sheet
			$objWriter->startElement('sheet');
			$objWriter->writeAttribute('name', 		$pSheetname);
			$objWriter->writeAttribute('sheetId', 	$pSheetId);
			if ($sheetState != 'visible' && $sheetState != '') {
				$objWriter->writeAttribute('state', $sheetState);
			}
			$objWriter->writeAttribute('r:id', 		'rId' . $pRelId);
			$objWriter->endElement();
		} else {
			throw new Generate_PHPExcel_Writer_Exception("Invalid parameters passed.");
		}
	}

	/**
	 * Write Defined Names
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	Generate_PHPExcel					$pGenerate_PHPExcel
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeDefinedNames(Generate_PHPExcel_Shared_XMLWriter $objWriter = null, Generate_PHPExcel $pGenerate_PHPExcel = null)
	{
		// Write defined names
		$objWriter->startElement('definedNames');

		// Named ranges
		if (count($pGenerate_PHPExcel->getNamedRanges()) > 0) {
			// Named ranges
			$this->_writeNamedRanges($objWriter, $pGenerate_PHPExcel);
		}

		// Other defined names
		$sheetCount = $pGenerate_PHPExcel->getSheetCount();
		for ($i = 0; $i < $sheetCount; ++$i) {
			// definedName for autoFilter
			$this->_writeDefinedNameForAutofilter($objWriter, $pGenerate_PHPExcel->getSheet($i), $i);

			// definedName for Print_Titles
			$this->_writeDefinedNameForPrintTitles($objWriter, $pGenerate_PHPExcel->getSheet($i), $i);

			// definedName for Print_Area
			$this->_writeDefinedNameForPrintArea($objWriter, $pGenerate_PHPExcel->getSheet($i), $i);
		}

		$objWriter->endElement();
	}

	/**
	 * Write named ranges
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	Generate_PHPExcel					$pGenerate_PHPExcel
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeNamedRanges(Generate_PHPExcel_Shared_XMLWriter $objWriter = null, Generate_PHPExcel $pGenerate_PHPExcel)
	{
		// Loop named ranges
		$namedRanges = $pGenerate_PHPExcel->getNamedRanges();
		foreach ($namedRanges as $namedRange) {
			$this->_writeDefinedNameForNamedRange($objWriter, $namedRange);
		}
	}

	/**
	 * Write Defined Name for named range
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	Generate_PHPExcel_NamedRange			$pNamedRange
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeDefinedNameForNamedRange(Generate_PHPExcel_Shared_XMLWriter $objWriter = null, Generate_PHPExcel_NamedRange $pNamedRange)
	{
		// definedName for named range
		$objWriter->startElement('definedName');
		$objWriter->writeAttribute('name',			$pNamedRange->getName());
		if ($pNamedRange->getLocalOnly()) {
			$objWriter->writeAttribute('localSheetId',	$pNamedRange->getScope()->getParent()->getIndex($pNamedRange->getScope()));
		}

		// Create absolute coordinate and write as raw text
		$range = Generate_PHPExcel_Cell::splitRange($pNamedRange->getRange());
		for ($i = 0; $i < count($range); $i++) {
			$range[$i][0] = '\'' . str_replace("'", "''", $pNamedRange->getWorksheet()->getTitle()) . '\'!' . Generate_PHPExcel_Cell::absoluteReference($range[$i][0]);
			if (isset($range[$i][1])) {
				$range[$i][1] = Generate_PHPExcel_Cell::absoluteReference($range[$i][1]);
			}
		}
		$range = Generate_PHPExcel_Cell::buildRange($range);

		$objWriter->writeRawData($range);

		$objWriter->endElement();
	}

	/**
	 * Write Defined Name for autoFilter
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	Generate_PHPExcel_Worksheet			$pSheet
	 * @param 	int							$pSheetId
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeDefinedNameForAutofilter(Generate_PHPExcel_Shared_XMLWriter $objWriter = null, Generate_PHPExcel_Worksheet $pSheet = null, $pSheetId = 0)
	{
		// definedName for autoFilter
		$autoFilterRange = $pSheet->getAutoFilter()->getRange();
		if (!empty($autoFilterRange)) {
			$objWriter->startElement('definedName');
			$objWriter->writeAttribute('name',			'_xlnm._FilterDatabase');
			$objWriter->writeAttribute('localSheetId',	$pSheetId);
			$objWriter->writeAttribute('hidden',		'1');

			// Create absolute coordinate and write as raw text
			$range = Generate_PHPExcel_Cell::splitRange($autoFilterRange);
			$range = $range[0];
			//	Strip any worksheet ref so we can make the cell ref absolute
			if (strpos($range[0],'!') !== false) {
				list($ws,$range[0]) = explode('!',$range[0]);
			}

			$range[0] = Generate_PHPExcel_Cell::absoluteCoordinate($range[0]);
			$range[1] = Generate_PHPExcel_Cell::absoluteCoordinate($range[1]);
			$range = implode(':', $range);

			$objWriter->writeRawData('\'' . str_replace("'", "''", $pSheet->getTitle()) . '\'!' . $range);

			$objWriter->endElement();
		}
	}

	/**
	 * Write Defined Name for PrintTitles
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	Generate_PHPExcel_Worksheet			$pSheet
	 * @param 	int							$pSheetId
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeDefinedNameForPrintTitles(Generate_PHPExcel_Shared_XMLWriter $objWriter = null, Generate_PHPExcel_Worksheet $pSheet = null, $pSheetId = 0)
	{
		// definedName for PrintTitles
		if ($pSheet->getPageSetup()->isColumnsToRepeatAtLeftSet() || $pSheet->getPageSetup()->isRowsToRepeatAtTopSet()) {
			$objWriter->startElement('definedName');
			$objWriter->writeAttribute('name',			'_xlnm.Print_Titles');
			$objWriter->writeAttribute('localSheetId',	$pSheetId);

			// Setting string
			$settingString = '';

			// Columns to repeat
			if ($pSheet->getPageSetup()->isColumnsToRepeatAtLeftSet()) {
				$repeat = $pSheet->getPageSetup()->getColumnsToRepeatAtLeft();

				$settingString .= '\'' . str_replace("'", "''", $pSheet->getTitle()) . '\'!$' . $repeat[0] . ':$' . $repeat[1];
			}

			// Rows to repeat
			if ($pSheet->getPageSetup()->isRowsToRepeatAtTopSet()) {
				if ($pSheet->getPageSetup()->isColumnsToRepeatAtLeftSet()) {
					$settingString .= ',';
				}

				$repeat = $pSheet->getPageSetup()->getRowsToRepeatAtTop();

				$settingString .= '\'' . str_replace("'", "''", $pSheet->getTitle()) . '\'!$' . $repeat[0] . ':$' . $repeat[1];
			}

			$objWriter->writeRawData($settingString);

			$objWriter->endElement();
		}
	}

	/**
	 * Write Defined Name for PrintTitles
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	Generate_PHPExcel_Worksheet			$pSheet
	 * @param 	int							$pSheetId
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeDefinedNameForPrintArea(Generate_PHPExcel_Shared_XMLWriter $objWriter = null, Generate_PHPExcel_Worksheet $pSheet = null, $pSheetId = 0)
	{
		// definedName for PrintArea
		if ($pSheet->getPageSetup()->isPrintAreaSet()) {
			$objWriter->startElement('definedName');
			$objWriter->writeAttribute('name',			'_xlnm.Print_Area');
			$objWriter->writeAttribute('localSheetId',	$pSheetId);

			// Setting string
			$settingString = '';

			// Print area
			$printArea = Generate_PHPExcel_Cell::splitRange($pSheet->getPageSetup()->getPrintArea());

			$chunks = array();
			foreach ($printArea as $printAreaRect) {
				$printAreaRect[0] = Generate_PHPExcel_Cell::absoluteReference($printAreaRect[0]);
				$printAreaRect[1] = Generate_PHPExcel_Cell::absoluteReference($printAreaRect[1]);
				$chunks[] = '\'' . str_replace("'", "''", $pSheet->getTitle()) . '\'!' . implode(':', $printAreaRect);
			}

			$objWriter->writeRawData(implode(',', $chunks));

			$objWriter->endElement();
		}
	}
}
