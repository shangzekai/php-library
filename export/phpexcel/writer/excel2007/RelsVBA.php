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
 * @version     1.8.0, 2014-03-02
 */


/**
 * Generate_PHPExcel_Writer_Excel2007_RelsVBA
 *
 * @category   Generate_PHPExcel
 * @package    Generate_PHPExcel_Writer_Excel2007
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
class Generate_PHPExcel_Writer_Excel2007_RelsVBA extends Generate_PHPExcel_Writer_Excel2007_WriterPart
{
	/**
	 * Write relationships for a signed VBA Project
	 *
	 * @param 	Generate_PHPExcel	$pGenerate_PHPExcel
	 * @return 	string 		XML Output
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	public function writeVBARelationships(Generate_PHPExcel $pGenerate_PHPExcel = null){
		// Create XML writer
		$objWriter = null;
		if ($this->getParentWriter()->getUseDiskCaching()) {
			$objWriter = new Generate_PHPExcel_Shared_XMLWriter(Generate_PHPExcel_Shared_XMLWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
		} else {
			$objWriter = new Generate_PHPExcel_Shared_XMLWriter(Generate_PHPExcel_Shared_XMLWriter::STORAGE_MEMORY);
		}

		// XML header
		$objWriter->startDocument('1.0','UTF-8','yes');

		// Relationships
		$objWriter->startElement('Relationships');
		$objWriter->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');
		$objWriter->startElement('Relationship');
		$objWriter->writeAttribute('Id', 'rId1');
		$objWriter->writeAttribute('Type', 'http://schemas.microsoft.com/office/2006/relationships/vbaProjectSignature');
		$objWriter->writeAttribute('Target', 'vbaProjectSignature.bin');
		$objWriter->endElement();//Relationship
		$objWriter->endElement();//Relationships

		// Return
		return $objWriter->getData();

	}

}
