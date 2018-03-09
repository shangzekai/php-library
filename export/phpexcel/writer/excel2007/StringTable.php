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
 * Generate_PHPExcel_Writer_Excel2007_StringTable
 *
 * @category   Generate_PHPExcel
 * @package    Generate_PHPExcel_Writer_Excel2007
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
class Generate_PHPExcel_Writer_Excel2007_StringTable extends Generate_PHPExcel_Writer_Excel2007_WriterPart
{
	/**
	 * Create worksheet stringtable
	 *
	 * @param 	Generate_PHPExcel_Worksheet 	$pSheet				Worksheet
	 * @param 	string[] 				$pExistingTable 	Existing table to eventually merge with
	 * @return 	string[] 				String table for worksheet
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	public function createStringTable($pSheet = null, $pExistingTable = null)
	{
		if ($pSheet !== NULL) {
			// Create string lookup table
			$aStringTable = array();
			$cellCollection = null;
			$aFlippedStringTable = null;	// For faster lookup

			// Is an existing table given?
			if (($pExistingTable !== NULL) && is_array($pExistingTable)) {
				$aStringTable = $pExistingTable;
			}

			// Fill index array
			$aFlippedStringTable = $this->flipStringTable($aStringTable);

			// Loop through cells
			foreach ($pSheet->getCellCollection() as $cellID) {
				$cell = $pSheet->getCell($cellID);
				$cellValue = $cell->getValue();
				if (!is_object($cellValue) &&
					($cellValue !== NULL) &&
					$cellValue !== '' &&
					!isset($aFlippedStringTable[$cellValue]) &&
					($cell->getDataType() == Generate_PHPExcel_Cell_DataType::TYPE_STRING || $cell->getDataType() == Generate_PHPExcel_Cell_DataType::TYPE_STRING2 || $cell->getDataType() == Generate_PHPExcel_Cell_DataType::TYPE_NULL)) {
						$aStringTable[] = $cellValue;
						$aFlippedStringTable[$cellValue] = true;
				} elseif ($cellValue instanceof Generate_PHPExcel_RichText &&
						  ($cellValue !== NULL) &&
						  !isset($aFlippedStringTable[$cellValue->getHashCode()])) {
								$aStringTable[] = $cellValue;
								$aFlippedStringTable[$cellValue->getHashCode()] = true;
	        	}
	        }

	        // Return
	        return $aStringTable;
		} else {
			throw new Generate_PHPExcel_Writer_Exception("Invalid Generate_PHPExcel_Worksheet object passed.");
		}
	}

	/**
	 * Write string table to XML format
	 *
	 * @param 	string[] 	$pStringTable
	 * @return 	string 		XML Output
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	public function writeStringTable($pStringTable = null)
	{
		if ($pStringTable !== NULL) {
			// Create XML writer
			$objWriter = null;
			if ($this->getParentWriter()->getUseDiskCaching()) {
				$objWriter = new Generate_PHPExcel_Shared_XMLWriter(Generate_PHPExcel_Shared_XMLWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
			} else {
				$objWriter = new Generate_PHPExcel_Shared_XMLWriter(Generate_PHPExcel_Shared_XMLWriter::STORAGE_MEMORY);
			}

			// XML header
			$objWriter->startDocument('1.0','UTF-8','yes');

			// String table
			$objWriter->startElement('sst');
			$objWriter->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
			$objWriter->writeAttribute('uniqueCount', count($pStringTable));

				// Loop through string table
				foreach ($pStringTable as $textElement) {
					$objWriter->startElement('si');

						if (! $textElement instanceof Generate_PHPExcel_RichText) {
							$textToWrite = Generate_PHPExcel_Shared_String::ControlCharacterPHP2OOXML( $textElement );
							$objWriter->startElement('t');
							if ($textToWrite !== trim($textToWrite)) {
								$objWriter->writeAttribute('xml:space', 'preserve');
							}
							$objWriter->writeRawData($textToWrite);
							$objWriter->endElement();
						} else if ($textElement instanceof Generate_PHPExcel_RichText) {
							$this->writeRichText($objWriter, $textElement);
						}

                    $objWriter->endElement();
				}

			$objWriter->endElement();

			// Return
			return $objWriter->getData();
		} else {
			throw new Generate_PHPExcel_Writer_Exception("Invalid string table array passed.");
		}
	}

	/**
	 * Write Rich Text
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	Generate_PHPExcel_RichText			$pRichText		Rich text
	 * @param 	string						$prefix			Optional Namespace prefix
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	public function writeRichText(Generate_PHPExcel_Shared_XMLWriter $objWriter = null, Generate_PHPExcel_RichText $pRichText = null, $prefix=NULL)
	{
		if ($prefix !== NULL)
			$prefix .= ':';
		// Loop through rich text elements
		$elements = $pRichText->getRichTextElements();
		foreach ($elements as $element) {
			// r
			$objWriter->startElement($prefix.'r');

				// rPr
				if ($element instanceof Generate_PHPExcel_RichText_Run) {
					// rPr
					$objWriter->startElement($prefix.'rPr');

						// rFont
						$objWriter->startElement($prefix.'rFont');
						$objWriter->writeAttribute('val', $element->getFont()->getName());
						$objWriter->endElement();

						// Bold
						$objWriter->startElement($prefix.'b');
						$objWriter->writeAttribute('val', ($element->getFont()->getBold() ? 'true' : 'false'));
						$objWriter->endElement();

						// Italic
						$objWriter->startElement($prefix.'i');
						$objWriter->writeAttribute('val', ($element->getFont()->getItalic() ? 'true' : 'false'));
						$objWriter->endElement();

						// Superscript / subscript
						if ($element->getFont()->getSuperScript() || $element->getFont()->getSubScript()) {
							$objWriter->startElement($prefix.'vertAlign');
							if ($element->getFont()->getSuperScript()) {
								$objWriter->writeAttribute('val', 'superscript');
							} else if ($element->getFont()->getSubScript()) {
								$objWriter->writeAttribute('val', 'subscript');
							}
							$objWriter->endElement();
						}

						// Strikethrough
						$objWriter->startElement($prefix.'strike');
						$objWriter->writeAttribute('val', ($element->getFont()->getStrikethrough() ? 'true' : 'false'));
						$objWriter->endElement();

						// Color
						$objWriter->startElement($prefix.'color');
						$objWriter->writeAttribute('rgb', $element->getFont()->getColor()->getARGB());
						$objWriter->endElement();

						// Size
						$objWriter->startElement($prefix.'sz');
						$objWriter->writeAttribute('val', $element->getFont()->getSize());
						$objWriter->endElement();

						// Underline
						$objWriter->startElement($prefix.'u');
						$objWriter->writeAttribute('val', $element->getFont()->getUnderline());
						$objWriter->endElement();

					$objWriter->endElement();
				}

				// t
				$objWriter->startElement($prefix.'t');
				$objWriter->writeAttribute('xml:space', 'preserve');
				$objWriter->writeRawData(Generate_PHPExcel_Shared_String::ControlCharacterPHP2OOXML( $element->getText() ));
				$objWriter->endElement();

			$objWriter->endElement();
		}
	}

	/**
	 * Write Rich Text
	 *
	 * @param 	Generate_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	string|Generate_PHPExcel_RichText	$pRichText		text string or Rich text
	 * @param 	string						$prefix			Optional Namespace prefix
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	public function writeRichTextForCharts(Generate_PHPExcel_Shared_XMLWriter $objWriter = null, $pRichText = null, $prefix=NULL)
	{
		if (!$pRichText instanceof Generate_PHPExcel_RichText) {
			$textRun = $pRichText;
			$pRichText = new Generate_PHPExcel_RichText();
			$pRichText->createTextRun($textRun);
		}

		if ($prefix !== NULL)
			$prefix .= ':';
		// Loop through rich text elements
		$elements = $pRichText->getRichTextElements();
		foreach ($elements as $element) {
			// r
			$objWriter->startElement($prefix.'r');

				// rPr
				$objWriter->startElement($prefix.'rPr');

					// Bold
					$objWriter->writeAttribute('b', ($element->getFont()->getBold() ? 1 : 0));
					// Italic
					$objWriter->writeAttribute('i', ($element->getFont()->getItalic() ? 1 : 0));
					// Underline
					$underlineType = $element->getFont()->getUnderline();
					switch($underlineType) {
						case 'single' :
							$underlineType = 'sng';
							break;
						case 'double' :
							$underlineType = 'dbl';
							break;
					}
					$objWriter->writeAttribute('u', $underlineType);
					// Strikethrough
					$objWriter->writeAttribute('strike', ($element->getFont()->getStrikethrough() ? 'sngStrike' : 'noStrike'));

					// rFont
					$objWriter->startElement($prefix.'latin');
						$objWriter->writeAttribute('typeface', $element->getFont()->getName());
					$objWriter->endElement();

					// Superscript / subscript
//					if ($element->getFont()->getSuperScript() || $element->getFont()->getSubScript()) {
//						$objWriter->startElement($prefix.'vertAlign');
//						if ($element->getFont()->getSuperScript()) {
//							$objWriter->writeAttribute('val', 'superscript');
//						} else if ($element->getFont()->getSubScript()) {
//							$objWriter->writeAttribute('val', 'subscript');
//						}
//						$objWriter->endElement();
//					}
//
				$objWriter->endElement();

				// t
				$objWriter->startElement($prefix.'t');
//					$objWriter->writeAttribute('xml:space', 'preserve');	//	Excel2010 accepts, Excel2007 complains
					$objWriter->writeRawData(Generate_PHPExcel_Shared_String::ControlCharacterPHP2OOXML( $element->getText() ));
				$objWriter->endElement();

			$objWriter->endElement();
		}
	}

	/**
	 * Flip string table (for index searching)
	 *
	 * @param 	array	$stringTable	Stringtable
	 * @return 	array
	 */
	public function flipStringTable($stringTable = array()) {
		// Return value
		$returnValue = array();

		// Loop through stringtable and add flipped items to $returnValue
		foreach ($stringTable as $key => $value) {
			if (! $value instanceof Generate_PHPExcel_RichText) {
				$returnValue[$value] = $key;
			} else if ($value instanceof Generate_PHPExcel_RichText) {
				$returnValue[$value->getHashCode()] = $key;
			}
		}

		// Return
		return $returnValue;
	}
}
