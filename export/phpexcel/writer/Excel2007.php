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
 * Generate_PHPExcel_Writer_Excel2007
 *
 * @category   Generate_PHPExcel
 * @package    Generate_PHPExcel_Writer_2007
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
class Generate_PHPExcel_Writer_Excel2007 extends Generate_PHPExcel_Writer_Abstract implements Generate_PHPExcel_Writer_IWriter
{
	/**
	 * Pre-calculate formulas
	 * Forces Generate_PHPExcel to recalculate all formulae in a workbook when saving, so that the pre-calculated values are
	 *    immediately available to MS Excel or other office spreadsheet viewer when opening the file
	 *
     * Overrides the default TRUE for this specific writer for performance reasons
     *
	 * @var boolean
	 */
	protected $_preCalculateFormulas = FALSE;

	/**
	 * Office2003 compatibility
	 *
	 * @var boolean
	 */
	private $_office2003compatibility = false;

	/**
	 * Private writer parts
	 *
	 * @var Generate_PHPExcel_Writer_Excel2007_WriterPart[]
	 */
	private $_writerParts	= array();

	/**
	 * Private Generate_PHPExcel
	 *
	 * @var Generate_PHPExcel
	 */
	private $_spreadSheet;

	/**
	 * Private string table
	 *
	 * @var string[]
	 */
	private $_stringTable	= array();

	/**
	 * Private unique Generate_PHPExcel_Style_Conditional HashTable
	 *
	 * @var Generate_PHPExcel_HashTable
	 */
	private $_stylesConditionalHashTable;

	/**
	 * Private unique Generate_PHPExcel_Style HashTable
	 *
	 * @var Generate_PHPExcel_HashTable
	 */
	private $_styleHashTable;

	/**
	 * Private unique Generate_PHPExcel_Style_Fill HashTable
	 *
	 * @var Generate_PHPExcel_HashTable
	 */
	private $_fillHashTable;

	/**
	 * Private unique Generate_PHPExcel_Style_Font HashTable
	 *
	 * @var Generate_PHPExcel_HashTable
	 */
	private $_fontHashTable;

	/**
	 * Private unique Generate_PHPExcel_Style_Borders HashTable
	 *
	 * @var Generate_PHPExcel_HashTable
	 */
	private $_bordersHashTable ;

	/**
	 * Private unique Generate_PHPExcel_Style_NumberFormat HashTable
	 *
	 * @var Generate_PHPExcel_HashTable
	 */
	private $_numFmtHashTable;

	/**
	 * Private unique Generate_PHPExcel_Worksheet_BaseDrawing HashTable
	 *
	 * @var Generate_PHPExcel_HashTable
	 */
	private $_drawingHashTable;

    /**
     * Create a new Generate_PHPExcel_Writer_Excel2007
     *
	 * @param 	Generate_PHPExcel	$pGenerate_PHPExcel
     */
    public function __construct(Generate_PHPExcel $pGenerate_PHPExcel = null)
    {
    	// Assign Generate_PHPExcel
		$this->setGenerate_PHPExcel($pGenerate_PHPExcel);

    	$writerPartsArray = array(	'stringtable'	=> 'Generate_PHPExcel_Writer_Excel2007_StringTable',
									'contenttypes'	=> 'Generate_PHPExcel_Writer_Excel2007_ContentTypes',
									'docprops' 		=> 'Generate_PHPExcel_Writer_Excel2007_DocProps',
									'rels'			=> 'Generate_PHPExcel_Writer_Excel2007_Rels',
									'theme' 		=> 'Generate_PHPExcel_Writer_Excel2007_Theme',
									'style' 		=> 'Generate_PHPExcel_Writer_Excel2007_Style',
									'workbook' 		=> 'Generate_PHPExcel_Writer_Excel2007_Workbook',
									'worksheet' 	=> 'Generate_PHPExcel_Writer_Excel2007_Worksheet',
									'drawing' 		=> 'Generate_PHPExcel_Writer_Excel2007_Drawing',
									'comments' 		=> 'Generate_PHPExcel_Writer_Excel2007_Comments',
									'chart'			=> 'Generate_PHPExcel_Writer_Excel2007_Chart',
									'relsvba'		=> 'Generate_PHPExcel_Writer_Excel2007_RelsVBA',
									'relsribbonobjects' => 'Generate_PHPExcel_Writer_Excel2007_RelsRibbon'
								 );

    	//	Initialise writer parts
		//		and Assign their parent IWriters
		foreach ($writerPartsArray as $writer => $class) {
			$this->_writerParts[$writer] = new $class($this);
		}

    	$hashTablesArray = array( '_stylesConditionalHashTable',	'_fillHashTable',		'_fontHashTable',
								  '_bordersHashTable',				'_numFmtHashTable',		'_drawingHashTable',
                                  '_styleHashTable'
							    );

		// Set HashTable variables
		foreach ($hashTablesArray as $tableName) {
			$this->$tableName 	= new Generate_PHPExcel_HashTable();
		}
    }

	/**
	 * Get writer part
	 *
	 * @param 	string 	$pPartName		Writer part name
	 * @return 	Generate_PHPExcel_Writer_Excel2007_WriterPart
	 */
	public function getWriterPart($pPartName = '') {
		if ($pPartName != '' && isset($this->_writerParts[strtolower($pPartName)])) {
			return $this->_writerParts[strtolower($pPartName)];
		} else {
			return null;
		}
	}

	/**
	 * Save Generate_PHPExcel to file
	 *
	 * @param 	string 		$pFilename
	 * @throws 	Generate_PHPExcel_Writer_Exception
	 */
	public function save($pFilename = null)
	{
		if ($this->_spreadSheet !== NULL) {
			// garbage collect
			$this->_spreadSheet->garbageCollect();

			// If $pFilename is php://output or php://stdout, make it a temporary file...
			$originalFilename = $pFilename;
			if (strtolower($pFilename) == 'php://output' || strtolower($pFilename) == 'php://stdout') {
				$pFilename = @tempnam(Generate_PHPExcel_Shared_File::sys_get_temp_dir(), 'phpxltmp');
				if ($pFilename == '') {
					$pFilename = $originalFilename;
				}
			}

			$saveDebugLog = Generate_PHPExcel_Calculation::getInstance($this->_spreadSheet)->getDebugLog()->getWriteDebugLog();
			Generate_PHPExcel_Calculation::getInstance($this->_spreadSheet)->getDebugLog()->setWriteDebugLog(FALSE);
			$saveDateReturnType = Generate_PHPExcel_Calculation_Functions::getReturnDateType();
			Generate_PHPExcel_Calculation_Functions::setReturnDateType(Generate_PHPExcel_Calculation_Functions::RETURNDATE_EXCEL);

			// Create string lookup table
			$this->_stringTable = array();
			for ($i = 0; $i < $this->_spreadSheet->getSheetCount(); ++$i) {
				$this->_stringTable = $this->getWriterPart('StringTable')->createStringTable($this->_spreadSheet->getSheet($i), $this->_stringTable);
			}

			// Create styles dictionaries
			$this->_styleHashTable->addFromSource( 	            $this->getWriterPart('Style')->allStyles($this->_spreadSheet) 			);
			$this->_stylesConditionalHashTable->addFromSource( 	$this->getWriterPart('Style')->allConditionalStyles($this->_spreadSheet) 			);
			$this->_fillHashTable->addFromSource( 				$this->getWriterPart('Style')->allFills($this->_spreadSheet) 			);
			$this->_fontHashTable->addFromSource( 				$this->getWriterPart('Style')->allFonts($this->_spreadSheet) 			);
			$this->_bordersHashTable->addFromSource( 			$this->getWriterPart('Style')->allBorders($this->_spreadSheet) 			);
			$this->_numFmtHashTable->addFromSource( 			$this->getWriterPart('Style')->allNumberFormats($this->_spreadSheet) 	);

			// Create drawing dictionary
			$this->_drawingHashTable->addFromSource( 			$this->getWriterPart('Drawing')->allDrawings($this->_spreadSheet) 		);

			// Create new ZIP file and open it for writing
			$zipClass = Generate_PHPExcel_Settings::getZipClass();
			$objZip = new $zipClass();

			//	Retrieve OVERWRITE and CREATE constants from the instantiated zip class
			//	This method of accessing constant values from a dynamic class should work with all appropriate versions of PHP
			$ro = new ReflectionObject($objZip);
			$zipOverWrite = $ro->getConstant('OVERWRITE');
			$zipCreate = $ro->getConstant('CREATE');

			if (file_exists($pFilename)) {
				unlink($pFilename);
			}
			// Try opening the ZIP file
			if ($objZip->open($pFilename, $zipOverWrite) !== true) {
				if ($objZip->open($pFilename, $zipCreate) !== true) {
					throw new Generate_PHPExcel_Writer_Exception("Could not open " . $pFilename . " for writing.");
				}
			}

			// Add [Content_Types].xml to ZIP file
			$objZip->addFromString('[Content_Types].xml', 			$this->getWriterPart('ContentTypes')->writeContentTypes($this->_spreadSheet, $this->_includeCharts));

			//if hasMacros, add the vbaProject.bin file, Certificate file(if exists)
			if($this->_spreadSheet->hasMacros()){
				$macrosCode=$this->_spreadSheet->getMacrosCode();
				if(!is_null($macrosCode)){// we have the code ?
					$objZip->addFromString('xl/vbaProject.bin', $macrosCode);//allways in 'xl', allways named vbaProject.bin
					if($this->_spreadSheet->hasMacrosCertificate()){//signed macros ?
						// Yes : add the certificate file and the related rels file
						$objZip->addFromString('xl/vbaProjectSignature.bin', $this->_spreadSheet->getMacrosCertificate());
						$objZip->addFromString('xl/_rels/vbaProject.bin.rels',
							$this->getWriterPart('RelsVBA')->writeVBARelationships($this->_spreadSheet));
					}
				}
			}
			//a custom UI in this workbook ? add it ("base" xml and additional objects (pictures) and rels)
			if($this->_spreadSheet->hasRibbon()){
				$tmpRibbonTarget=$this->_spreadSheet->getRibbonXMLData('target');
				$objZip->addFromString($tmpRibbonTarget, $this->_spreadSheet->getRibbonXMLData('data'));
				if($this->_spreadSheet->hasRibbonBinObjects()){
					$tmpRootPath=dirname($tmpRibbonTarget).'/';
					$ribbonBinObjects=$this->_spreadSheet->getRibbonBinObjects('data');//the files to write
					foreach($ribbonBinObjects as $aPath=>$aContent){
						$objZip->addFromString($tmpRootPath.$aPath, $aContent);
					}
					//the rels for files
					$objZip->addFromString($tmpRootPath.'_rels/'.basename($tmpRibbonTarget).'.rels',
						$this->getWriterPart('RelsRibbonObjects')->writeRibbonRelationships($this->_spreadSheet));
				}
			}
			
			// Add relationships to ZIP file
			$objZip->addFromString('_rels/.rels', 					$this->getWriterPart('Rels')->writeRelationships($this->_spreadSheet));
			$objZip->addFromString('xl/_rels/workbook.xml.rels', 	$this->getWriterPart('Rels')->writeWorkbookRelationships($this->_spreadSheet));

			// Add document properties to ZIP file
			$objZip->addFromString('docProps/app.xml', 				$this->getWriterPart('DocProps')->writeDocPropsApp($this->_spreadSheet));
			$objZip->addFromString('docProps/core.xml', 			$this->getWriterPart('DocProps')->writeDocPropsCore($this->_spreadSheet));
			$customPropertiesPart = $this->getWriterPart('DocProps')->writeDocPropsCustom($this->_spreadSheet);
			if ($customPropertiesPart !== NULL) {
				$objZip->addFromString('docProps/custom.xml', 		$customPropertiesPart);
			}

			// Add theme to ZIP file
			$objZip->addFromString('xl/theme/theme1.xml', 			$this->getWriterPart('Theme')->writeTheme($this->_spreadSheet));

			// Add string table to ZIP file
			$objZip->addFromString('xl/sharedStrings.xml', 			$this->getWriterPart('StringTable')->writeStringTable($this->_stringTable));

			// Add styles to ZIP file
			$objZip->addFromString('xl/styles.xml', 				$this->getWriterPart('Style')->writeStyles($this->_spreadSheet));

			// Add workbook to ZIP file
			$objZip->addFromString('xl/workbook.xml', 				$this->getWriterPart('Workbook')->writeWorkbook($this->_spreadSheet, $this->_preCalculateFormulas));

			$chartCount = 0;
			// Add worksheets
			for ($i = 0; $i < $this->_spreadSheet->getSheetCount(); ++$i) {
				$objZip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $this->getWriterPart('Worksheet')->writeWorksheet($this->_spreadSheet->getSheet($i), $this->_stringTable, $this->_includeCharts));
				if ($this->_includeCharts) {
					$charts = $this->_spreadSheet->getSheet($i)->getChartCollection();
					if (count($charts) > 0) {
						foreach($charts as $chart) {
							$objZip->addFromString('xl/charts/chart' . ($chartCount + 1) . '.xml', $this->getWriterPart('Chart')->writeChart($chart));
							$chartCount++;
						}
					}
				}
			}

			$chartRef1 = $chartRef2 = 0;
			// Add worksheet relationships (drawings, ...)
			for ($i = 0; $i < $this->_spreadSheet->getSheetCount(); ++$i) {

				// Add relationships
				$objZip->addFromString('xl/worksheets/_rels/sheet' . ($i + 1) . '.xml.rels', 	$this->getWriterPart('Rels')->writeWorksheetRelationships($this->_spreadSheet->getSheet($i), ($i + 1), $this->_includeCharts));

				$drawings = $this->_spreadSheet->getSheet($i)->getDrawingCollection();
				$drawingCount = count($drawings);
				if ($this->_includeCharts) {
					$chartCount = $this->_spreadSheet->getSheet($i)->getChartCount();
				}

				// Add drawing and image relationship parts
				if (($drawingCount > 0) || ($chartCount > 0)) {
					// Drawing relationships
					$objZip->addFromString('xl/drawings/_rels/drawing' . ($i + 1) . '.xml.rels', $this->getWriterPart('Rels')->writeDrawingRelationships($this->_spreadSheet->getSheet($i),$chartRef1, $this->_includeCharts));

					// Drawings
					$objZip->addFromString('xl/drawings/drawing' . ($i + 1) . '.xml', $this->getWriterPart('Drawing')->writeDrawings($this->_spreadSheet->getSheet($i),$chartRef2,$this->_includeCharts));
				}

				// Add comment relationship parts
				if (count($this->_spreadSheet->getSheet($i)->getComments()) > 0) {
					// VML Comments
					$objZip->addFromString('xl/drawings/vmlDrawing' . ($i + 1) . '.vml', $this->getWriterPart('Comments')->writeVMLComments($this->_spreadSheet->getSheet($i)));

					// Comments
					$objZip->addFromString('xl/comments' . ($i + 1) . '.xml', $this->getWriterPart('Comments')->writeComments($this->_spreadSheet->getSheet($i)));
				}

				// Add header/footer relationship parts
				if (count($this->_spreadSheet->getSheet($i)->getHeaderFooter()->getImages()) > 0) {
					// VML Drawings
					$objZip->addFromString('xl/drawings/vmlDrawingHF' . ($i + 1) . '.vml', $this->getWriterPart('Drawing')->writeVMLHeaderFooterImages($this->_spreadSheet->getSheet($i)));

					// VML Drawing relationships
					$objZip->addFromString('xl/drawings/_rels/vmlDrawingHF' . ($i + 1) . '.vml.rels', $this->getWriterPart('Rels')->writeHeaderFooterDrawingRelationships($this->_spreadSheet->getSheet($i)));

					// Media
					foreach ($this->_spreadSheet->getSheet($i)->getHeaderFooter()->getImages() as $image) {
						$objZip->addFromString('xl/media/' . $image->getIndexedFilename(), file_get_contents($image->getPath()));
					}
				}
			}

			// Add media
			for ($i = 0; $i < $this->getDrawingHashTable()->count(); ++$i) {
				if ($this->getDrawingHashTable()->getByIndex($i) instanceof Generate_PHPExcel_Worksheet_Drawing) {
					$imageContents = null;
					$imagePath = $this->getDrawingHashTable()->getByIndex($i)->getPath();
					if (strpos($imagePath, 'zip://') !== false) {
						$imagePath = substr($imagePath, 6);
						$imagePathSplitted = explode('#', $imagePath);

						$imageZip = new ZipArchive();
						$imageZip->open($imagePathSplitted[0]);
						$imageContents = $imageZip->getFromName($imagePathSplitted[1]);
						$imageZip->close();
						unset($imageZip);
					} else {
						$imageContents = file_get_contents($imagePath);
					}

					$objZip->addFromString('xl/media/' . str_replace(' ', '_', $this->getDrawingHashTable()->getByIndex($i)->getIndexedFilename()), $imageContents);
				} else if ($this->getDrawingHashTable()->getByIndex($i) instanceof Generate_PHPExcel_Worksheet_MemoryDrawing) {
					ob_start();
					call_user_func(
						$this->getDrawingHashTable()->getByIndex($i)->getRenderingFunction(),
						$this->getDrawingHashTable()->getByIndex($i)->getImageResource()
					);
					$imageContents = ob_get_contents();
					ob_end_clean();

					$objZip->addFromString('xl/media/' . str_replace(' ', '_', $this->getDrawingHashTable()->getByIndex($i)->getIndexedFilename()), $imageContents);
				}
			}

			Generate_PHPExcel_Calculation_Functions::setReturnDateType($saveDateReturnType);
			Generate_PHPExcel_Calculation::getInstance($this->_spreadSheet)->getDebugLog()->setWriteDebugLog($saveDebugLog);

			// Close file
			if ($objZip->close() === false) {
				throw new Generate_PHPExcel_Writer_Exception("Could not close zip file $pFilename.");
			}

			// If a temporary file was used, copy it to the correct file stream
			if ($originalFilename != $pFilename) {
				if (copy($pFilename, $originalFilename) === false) {
					throw new Generate_PHPExcel_Writer_Exception("Could not copy temporary zip file $pFilename to $originalFilename.");
				}
				@unlink($pFilename);
			}
		} else {
			throw new Generate_PHPExcel_Writer_Exception("Generate_PHPExcel object unassigned.");
		}
	}

	/**
	 * Get Generate_PHPExcel object
	 *
	 * @return Generate_PHPExcel
	 * @throws Generate_PHPExcel_Writer_Exception
	 */
	public function getGenerate_PHPExcel() {
		if ($this->_spreadSheet !== null) {
			return $this->_spreadSheet;
		} else {
			throw new Generate_PHPExcel_Writer_Exception("No Generate_PHPExcel assigned.");
		}
	}

	/**
	 * Set Generate_PHPExcel object
	 *
	 * @param 	Generate_PHPExcel 	$pGenerate_PHPExcel	Generate_PHPExcel object
	 * @throws	Generate_PHPExcel_Writer_Exception
	 * @return Generate_PHPExcel_Writer_Excel2007
	 */
	public function setGenerate_PHPExcel(Generate_PHPExcel $pGenerate_PHPExcel = null) {
		$this->_spreadSheet = $pGenerate_PHPExcel;
		return $this;
	}

    /**
     * Get string table
     *
     * @return string[]
     */
    public function getStringTable() {
    	return $this->_stringTable;
    }

    /**
     * Get Generate_PHPExcel_Style HashTable
     *
     * @return Generate_PHPExcel_HashTable
     */
    public function getStyleHashTable() {
    	return $this->_styleHashTable;
    }

    /**
     * Get Generate_PHPExcel_Style_Conditional HashTable
     *
     * @return Generate_PHPExcel_HashTable
     */
    public function getStylesConditionalHashTable() {
    	return $this->_stylesConditionalHashTable;
    }

    /**
     * Get Generate_PHPExcel_Style_Fill HashTable
     *
     * @return Generate_PHPExcel_HashTable
     */
    public function getFillHashTable() {
    	return $this->_fillHashTable;
    }

    /**
     * Get Generate_PHPExcel_Style_Font HashTable
     *
     * @return Generate_PHPExcel_HashTable
     */
    public function getFontHashTable() {
    	return $this->_fontHashTable;
    }

    /**
     * Get Generate_PHPExcel_Style_Borders HashTable
     *
     * @return Generate_PHPExcel_HashTable
     */
    public function getBordersHashTable() {
    	return $this->_bordersHashTable;
    }

    /**
     * Get Generate_PHPExcel_Style_NumberFormat HashTable
     *
     * @return Generate_PHPExcel_HashTable
     */
    public function getNumFmtHashTable() {
    	return $this->_numFmtHashTable;
    }

    /**
     * Get Generate_PHPExcel_Worksheet_BaseDrawing HashTable
     *
     * @return Generate_PHPExcel_HashTable
     */
    public function getDrawingHashTable() {
    	return $this->_drawingHashTable;
    }

    /**
     * Get Office2003 compatibility
     *
     * @return boolean
     */
    public function getOffice2003Compatibility() {
    	return $this->_office2003compatibility;
    }

    /**
     * Set Office2003 compatibility
     *
     * @param boolean $pValue	Office2003 compatibility?
     * @return Generate_PHPExcel_Writer_Excel2007
     */
    public function setOffice2003Compatibility($pValue = false) {
    	$this->_office2003compatibility = $pValue;
    	return $this;
    }

}
