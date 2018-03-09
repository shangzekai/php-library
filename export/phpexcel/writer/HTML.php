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
 * @package	Generate_PHPExcel_Writer_HTML
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 * @license	http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version	1.8.0, 2014-03-02
 */


/**
 * Generate_PHPExcel_Writer_HTML
 *
 * @category   Generate_PHPExcel
 * @package	Generate_PHPExcel_Writer_HTML
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
class Generate_PHPExcel_Writer_HTML extends Generate_PHPExcel_Writer_Abstract implements Generate_PHPExcel_Writer_IWriter {
	/**
	 * Generate_PHPExcel object
	 *
	 * @var Generate_PHPExcel
	 */
	protected $_Generate_PHPExcel;

	/**
	 * Sheet index to write
	 *
	 * @var int
	 */
	private $_sheetIndex	= 0;

	/**
	 * Images root
	 *
	 * @var string
	 */
	private $_imagesRoot	= '.';

	/**
	 * embed images, or link to images
	 *
	 * @var boolean
	 */
	private $_embedImages	= FALSE;

	/**
	 * Use inline CSS?
	 *
	 * @var boolean
	 */
	private $_useInlineCss = false;

	/**
	 * Array of CSS styles
	 *
	 * @var array
	 */
	private $_cssStyles = null;

	/**
	 * Array of column widths in points
	 *
	 * @var array
	 */
	private $_columnWidths = null;

	/**
	 * Default font
	 *
	 * @var Generate_PHPExcel_Style_Font
	 */
	private $_defaultFont;

	/**
	 * Flag whether spans have been calculated
	 *
	 * @var boolean
	 */
	private $_spansAreCalculated	= false;

	/**
	 * Excel cells that should not be written as HTML cells
	 *
	 * @var array
	 */
	private $_isSpannedCell	= array();

	/**
	 * Excel cells that are upper-left corner in a cell merge
	 *
	 * @var array
	 */
	private $_isBaseCell	= array();

	/**
	 * Excel rows that should not be written as HTML rows
	 *
	 * @var array
	 */
	private $_isSpannedRow	= array();

	/**
	 * Is the current writer creating PDF?
	 *
	 * @var boolean
	 */
	protected $_isPdf = false;

	/**
	 * Generate the Navigation block
	 *
	 * @var boolean
	 */
	private $_generateSheetNavigationBlock = true;

	/**
	 * Create a new Generate_PHPExcel_Writer_HTML
	 *
	 * @param	Generate_PHPExcel	$Generate_PHPExcel	Generate_PHPExcel object
	 */
	public function __construct(Generate_PHPExcel $Generate_PHPExcel) {
		$this->_Generate_PHPExcel = $Generate_PHPExcel;
		$this->_defaultFont = $this->_Generate_PHPExcel->getDefaultStyle()->getFont();
	}

	/**
	 * Save Generate_PHPExcel to file
	 *
	 * @param	string		$pFilename
	 * @throws	Generate_PHPExcel_Writer_Exception
	 */
	public function save($pFilename = null) {
		// garbage collect
		$this->_Generate_PHPExcel->garbageCollect();

		$saveDebugLog = Generate_PHPExcel_Calculation::getInstance($this->_Generate_PHPExcel)->getDebugLog()->getWriteDebugLog();
		Generate_PHPExcel_Calculation::getInstance($this->_Generate_PHPExcel)->getDebugLog()->setWriteDebugLog(FALSE);
		$saveArrayReturnType = Generate_PHPExcel_Calculation::getArrayReturnType();
		Generate_PHPExcel_Calculation::setArrayReturnType(Generate_PHPExcel_Calculation::RETURN_ARRAY_AS_VALUE);

		// Build CSS
		$this->buildCSS(!$this->_useInlineCss);

		// Open file
		$fileHandle = fopen($pFilename, 'wb+');
		if ($fileHandle === false) {
			throw new Generate_PHPExcel_Writer_Exception("Could not open file $pFilename for writing.");
		}

		// Write headers
		fwrite($fileHandle, $this->generateHTMLHeader(!$this->_useInlineCss));

		// Write navigation (tabs)
		if ((!$this->_isPdf) && ($this->_generateSheetNavigationBlock)) {
			fwrite($fileHandle, $this->generateNavigation());
		}

		// Write data
		fwrite($fileHandle, $this->generateSheetData());

		// Write footer
		fwrite($fileHandle, $this->generateHTMLFooter());

		// Close file
		fclose($fileHandle);

		Generate_PHPExcel_Calculation::setArrayReturnType($saveArrayReturnType);
		Generate_PHPExcel_Calculation::getInstance($this->_Generate_PHPExcel)->getDebugLog()->setWriteDebugLog($saveDebugLog);
	}

	/**
	 * Map VAlign
	 *
	 * @param	string		$vAlign		Vertical alignment
	 * @return string
	 */
	private function _mapVAlign($vAlign) {
		switch ($vAlign) {
			case Generate_PHPExcel_Style_Alignment::VERTICAL_BOTTOM:		return 'bottom';
			case Generate_PHPExcel_Style_Alignment::VERTICAL_TOP:		return 'top';
			case Generate_PHPExcel_Style_Alignment::VERTICAL_CENTER:
			case Generate_PHPExcel_Style_Alignment::VERTICAL_JUSTIFY:	return 'middle';
			default: return 'baseline';
		}
	}

	/**
	 * Map HAlign
	 *
	 * @param	string		$hAlign		Horizontal alignment
	 * @return string|false
	 */
	private function _mapHAlign($hAlign) {
		switch ($hAlign) {
			case Generate_PHPExcel_Style_Alignment::HORIZONTAL_GENERAL:				return false;
			case Generate_PHPExcel_Style_Alignment::HORIZONTAL_LEFT:					return 'left';
			case Generate_PHPExcel_Style_Alignment::HORIZONTAL_RIGHT:				return 'right';
			case Generate_PHPExcel_Style_Alignment::HORIZONTAL_CENTER:
			case Generate_PHPExcel_Style_Alignment::HORIZONTAL_CENTER_CONTINUOUS:	return 'center';
			case Generate_PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY:				return 'justify';
			default: return false;
		}
	}

	/**
	 * Map border style
	 *
	 * @param	int		$borderStyle		Sheet index
	 * @return	string
	 */
	private function _mapBorderStyle($borderStyle) {
		switch ($borderStyle) {
			case Generate_PHPExcel_Style_Border::BORDER_NONE:				return 'none';
			case Generate_PHPExcel_Style_Border::BORDER_DASHDOT:				return '1px dashed';
			case Generate_PHPExcel_Style_Border::BORDER_DASHDOTDOT:			return '1px dotted';
			case Generate_PHPExcel_Style_Border::BORDER_DASHED:				return '1px dashed';
			case Generate_PHPExcel_Style_Border::BORDER_DOTTED:				return '1px dotted';
			case Generate_PHPExcel_Style_Border::BORDER_DOUBLE:				return '3px double';
			case Generate_PHPExcel_Style_Border::BORDER_HAIR:				return '1px solid';
			case Generate_PHPExcel_Style_Border::BORDER_MEDIUM:				return '2px solid';
			case Generate_PHPExcel_Style_Border::BORDER_MEDIUMDASHDOT:		return '2px dashed';
			case Generate_PHPExcel_Style_Border::BORDER_MEDIUMDASHDOTDOT:	return '2px dotted';
			case Generate_PHPExcel_Style_Border::BORDER_MEDIUMDASHED:		return '2px dashed';
			case Generate_PHPExcel_Style_Border::BORDER_SLANTDASHDOT:		return '2px dashed';
			case Generate_PHPExcel_Style_Border::BORDER_THICK:				return '3px solid';
			case Generate_PHPExcel_Style_Border::BORDER_THIN:				return '1px solid';
			default: return '1px solid'; // map others to thin
		}
	}

	/**
	 * Get sheet index
	 *
	 * @return int
	 */
	public function getSheetIndex() {
		return $this->_sheetIndex;
	}

	/**
	 * Set sheet index
	 *
	 * @param	int		$pValue		Sheet index
	 * @return Generate_PHPExcel_Writer_HTML
	 */
	public function setSheetIndex($pValue = 0) {
		$this->_sheetIndex = $pValue;
		return $this;
	}

	/**
	 * Get sheet index
	 *
	 * @return boolean
	 */
	public function getGenerateSheetNavigationBlock() {
		return $this->_generateSheetNavigationBlock;
	}

	/**
	 * Set sheet index
	 *
	 * @param	boolean		$pValue		Flag indicating whether the sheet navigation block should be generated or not
	 * @return Generate_PHPExcel_Writer_HTML
	 */
	public function setGenerateSheetNavigationBlock($pValue = true) {
		$this->_generateSheetNavigationBlock = (bool) $pValue;
		return $this;
	}

	/**
	 * Write all sheets (resets sheetIndex to NULL)
	 */
	public function writeAllSheets() {
		$this->_sheetIndex = null;
		return $this;
	}

	/**
	 * Generate HTML header
	 *
	 * @param	boolean		$pIncludeStyles		Include styles?
	 * @return	string
	 * @throws Generate_PHPExcel_Writer_Exception
	 */
	public function generateHTMLHeader($pIncludeStyles = false) {
		// Generate_PHPExcel object known?
		if (is_null($this->_Generate_PHPExcel)) {
			throw new Generate_PHPExcel_Writer_Exception('Internal Generate_PHPExcel object not set to an instance of an object.');
		}

		// Construct HTML
		$properties = $this->_Generate_PHPExcel->getProperties();
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">' . PHP_EOL;
		$html .= '<!-- Generated by Generate_PHPExcel - http://www.Generate_PHPExcel.net -->' . PHP_EOL;
		$html .= '<html>' . PHP_EOL;
		$html .= '  <head>' . PHP_EOL;
		$html .= '	  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . PHP_EOL;
		if ($properties->getTitle() > '')
			$html .= '	  <title>' . htmlspecialchars($properties->getTitle()) . '</title>' . PHP_EOL;

		if ($properties->getCreator() > '')
			$html .= '	  <meta name="author" content="' . htmlspecialchars($properties->getCreator()) . '" />' . PHP_EOL;
		if ($properties->getTitle() > '')
			$html .= '	  <meta name="title" content="' . htmlspecialchars($properties->getTitle()) . '" />' . PHP_EOL;
		if ($properties->getDescription() > '')
			$html .= '	  <meta name="description" content="' . htmlspecialchars($properties->getDescription()) . '" />' . PHP_EOL;
		if ($properties->getSubject() > '')
			$html .= '	  <meta name="subject" content="' . htmlspecialchars($properties->getSubject()) . '" />' . PHP_EOL;
		if ($properties->getKeywords() > '')
			$html .= '	  <meta name="keywords" content="' . htmlspecialchars($properties->getKeywords()) . '" />' . PHP_EOL;
		if ($properties->getCategory() > '')
			$html .= '	  <meta name="category" content="' . htmlspecialchars($properties->getCategory()) . '" />' . PHP_EOL;
		if ($properties->getCompany() > '')
			$html .= '	  <meta name="company" content="' . htmlspecialchars($properties->getCompany()) . '" />' . PHP_EOL;
		if ($properties->getManager() > '')
			$html .= '	  <meta name="manager" content="' . htmlspecialchars($properties->getManager()) . '" />' . PHP_EOL;

		if ($pIncludeStyles) {
			$html .= $this->generateStyles(true);
		}

		$html .= '  </head>' . PHP_EOL;
		$html .= '' . PHP_EOL;
		$html .= '  <body>' . PHP_EOL;

		// Return
		return $html;
	}

	/**
	 * Generate sheet data
	 *
	 * @return	string
	 * @throws Generate_PHPExcel_Writer_Exception
	 */
	public function generateSheetData() {
		// Generate_PHPExcel object known?
		if (is_null($this->_Generate_PHPExcel)) {
			throw new Generate_PHPExcel_Writer_Exception('Internal Generate_PHPExcel object not set to an instance of an object.');
		}

		// Ensure that Spans have been calculated?
		if (!$this->_spansAreCalculated) {
			$this->_calculateSpans();
		}

		// Fetch sheets
		$sheets = array();
		if (is_null($this->_sheetIndex)) {
			$sheets = $this->_Generate_PHPExcel->getAllSheets();
		} else {
			$sheets[] = $this->_Generate_PHPExcel->getSheet($this->_sheetIndex);
		}

		// Construct HTML
		$html = '';

		// Loop all sheets
		$sheetId = 0;
		foreach ($sheets as $sheet) {
			// Write table header
			$html .= $this->_generateTableHeader($sheet);

			// Get worksheet dimension
			$dimension = explode(':', $sheet->calculateWorksheetDimension());
			$dimension[0] = Generate_PHPExcel_Cell::coordinateFromString($dimension[0]);
			$dimension[0][0] = Generate_PHPExcel_Cell::columnIndexFromString($dimension[0][0]) - 1;
			$dimension[1] = Generate_PHPExcel_Cell::coordinateFromString($dimension[1]);
			$dimension[1][0] = Generate_PHPExcel_Cell::columnIndexFromString($dimension[1][0]) - 1;

			// row min,max
			$rowMin = $dimension[0][1];
			$rowMax = $dimension[1][1];

			// calculate start of <tbody>, <thead>
			$tbodyStart = $rowMin;
			$theadStart = $theadEnd   = 0; // default: no <thead>	no </thead>
			if ($sheet->getPageSetup()->isRowsToRepeatAtTopSet()) {
				$rowsToRepeatAtTop = $sheet->getPageSetup()->getRowsToRepeatAtTop();

				// we can only support repeating rows that start at top row
				if ($rowsToRepeatAtTop[0] == 1) {
					$theadStart = $rowsToRepeatAtTop[0];
					$theadEnd   = $rowsToRepeatAtTop[1];
					$tbodyStart = $rowsToRepeatAtTop[1] + 1;
				}
			}

			// Loop through cells
			$row = $rowMin-1;
			while($row++ < $rowMax) {
				// <thead> ?
				if ($row == $theadStart) {
					$html .= '		<thead>' . PHP_EOL;
				}

				// <tbody> ?
				if ($row == $tbodyStart) {
					$html .= '		<tbody>' . PHP_EOL;
				}

				// Write row if there are HTML table cells in it
				if ( !isset($this->_isSpannedRow[$sheet->getParent()->getIndex($sheet)][$row]) ) {
					// Start a new rowData
					$rowData = array();
					// Loop through columns
					$column = $dimension[0][0] - 1;
					while($column++ < $dimension[1][0]) {
						// Cell exists?
						if ($sheet->cellExistsByColumnAndRow($column, $row)) {
							$rowData[$column] = Generate_PHPExcel_Cell::stringFromColumnIndex($column) . $row;
						} else {
							$rowData[$column] = '';
						}
					}
					$html .= $this->_generateRow($sheet, $rowData, $row - 1);
				}

				// </thead> ?
				if ($row == $theadEnd) {
					$html .= '		</thead>' . PHP_EOL;
				}
			}
			$html .= $this->_extendRowsForChartsAndImages($sheet, $row);

			// Close table body.
			$html .= '		</tbody>' . PHP_EOL;

			// Write table footer
			$html .= $this->_generateTableFooter();

			// Writing PDF?
			if ($this->_isPdf) {
				if (is_null($this->_sheetIndex) && $sheetId + 1 < $this->_Generate_PHPExcel->getSheetCount()) {
					$html .= '<div style="page-break-before:always" />';
				}
			}

			// Next sheet
			++$sheetId;
		}

		// Return
		return $html;
	}

	/**
	 * Generate sheet tabs
	 *
	 * @return	string
	 * @throws Generate_PHPExcel_Writer_Exception
	 */
	public function generateNavigation()
	{
		// Generate_PHPExcel object known?
		if (is_null($this->_Generate_PHPExcel)) {
			throw new Generate_PHPExcel_Writer_Exception('Internal Generate_PHPExcel object not set to an instance of an object.');
		}

		// Fetch sheets
		$sheets = array();
		if (is_null($this->_sheetIndex)) {
			$sheets = $this->_Generate_PHPExcel->getAllSheets();
		} else {
			$sheets[] = $this->_Generate_PHPExcel->getSheet($this->_sheetIndex);
		}

		// Construct HTML
		$html = '';

		// Only if there are more than 1 sheets
		if (count($sheets) > 1) {
			// Loop all sheets
			$sheetId = 0;

			$html .= '<ul class="navigation">' . PHP_EOL;

			foreach ($sheets as $sheet) {
				$html .= '  <li class="sheet' . $sheetId . '"><a href="#sheet' . $sheetId . '">' . $sheet->getTitle() . '</a></li>' . PHP_EOL;
				++$sheetId;
			}

			$html .= '</ul>' . PHP_EOL;
		}

		return $html;
	}

	private function _extendRowsForChartsAndImages(Generate_PHPExcel_Worksheet $pSheet, $row) {
		$rowMax = $row;
		$colMax = 'A';
		if ($this->_includeCharts) {
			foreach ($pSheet->getChartCollection() as $chart) {
				if ($chart instanceof Generate_PHPExcel_Chart) {
				    $chartCoordinates = $chart->getTopLeftPosition();
				    $chartTL = Generate_PHPExcel_Cell::coordinateFromString($chartCoordinates['cell']);
					$chartCol = Generate_PHPExcel_Cell::columnIndexFromString($chartTL[0]);
					if ($chartTL[1] > $rowMax) {
						$rowMax = $chartTL[1];
						if ($chartCol > Generate_PHPExcel_Cell::columnIndexFromString($colMax)) {
							$colMax = $chartTL[0];
						}
					}
				}
			}
		}

		foreach ($pSheet->getDrawingCollection() as $drawing) {
			if ($drawing instanceof Generate_PHPExcel_Worksheet_Drawing) {
			    $imageTL = Generate_PHPExcel_Cell::coordinateFromString($drawing->getCoordinates());
				$imageCol = Generate_PHPExcel_Cell::columnIndexFromString($imageTL[0]);
				if ($imageTL[1] > $rowMax) {
					$rowMax = $imageTL[1];
					if ($imageCol > Generate_PHPExcel_Cell::columnIndexFromString($colMax)) {
						$colMax = $imageTL[0];
					}
				}
			}
		}
		$html = '';
		$colMax++;
		while ($row < $rowMax) {
			$html .= '<tr>';
			for ($col = 'A'; $col != $colMax; ++$col) {
				$html .= '<td>';
				$html .= $this->_writeImageInCell($pSheet, $col.$row);
				if ($this->_includeCharts) {
					$html .= $this->_writeChartInCell($pSheet, $col.$row);
				}
				$html .= '</td>';
			}
			++$row;
			$html .= '</tr>';
		}
		return $html;
	}


	/**
	 * Generate image tag in cell
	 *
	 * @param	Generate_PHPExcel_Worksheet	$pSheet			Generate_PHPExcel_Worksheet
	 * @param	string				$coordinates	Cell coordinates
	 * @return	string
	 * @throws	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeImageInCell(Generate_PHPExcel_Worksheet $pSheet, $coordinates) {
		// Construct HTML
		$html = '';

		// Write images
		foreach ($pSheet->getDrawingCollection() as $drawing) {
			if ($drawing instanceof Generate_PHPExcel_Worksheet_Drawing) {
				if ($drawing->getCoordinates() == $coordinates) {
					$filename = $drawing->getPath();

					// Strip off eventual '.'
					if (substr($filename, 0, 1) == '.') {
						$filename = substr($filename, 1);
					}

					// Prepend images root
					$filename = $this->getImagesRoot() . $filename;

					// Strip off eventual '.'
					if (substr($filename, 0, 1) == '.' && substr($filename, 0, 2) != './') {
						$filename = substr($filename, 1);
					}

					// Convert UTF8 data to PCDATA
					$filename = htmlspecialchars($filename);

					$html .= PHP_EOL;
					if ((!$this->_embedImages) || ($this->_isPdf)) {
						$imageData = $filename;
					} else {
						$imageDetails = getimagesize($filename);
						if ($fp = fopen($filename,"rb", 0)) {
							$picture = fread($fp,filesize($filename));
							fclose($fp);
							// base64 encode the binary data, then break it
							// into chunks according to RFC 2045 semantics
							$base64 = chunk_split(base64_encode($picture));
							$imageData = 'data:'.$imageDetails['mime'].';base64,' . $base64;
						} else {
							$imageData = $filename;
						}
					}

					$html .= '<div style="position: relative;">';
					$html .= '<img style="position: absolute; z-index: 1; left: ' . 
                        $drawing->getOffsetX() . 'px; top: ' . $drawing->getOffsetY() . 'px; width: ' . 
                        $drawing->getWidth() . 'px; height: ' . $drawing->getHeight() . 'px;" src="' . 
                        $imageData . '" border="0" />';
					$html .= '</div>';
				}
			}
		}

		// Return
		return $html;
	}

	/**
	 * Generate chart tag in cell
	 *
	 * @param	Generate_PHPExcel_Worksheet	$pSheet			Generate_PHPExcel_Worksheet
	 * @param	string				$coordinates	Cell coordinates
	 * @return	string
	 * @throws	Generate_PHPExcel_Writer_Exception
	 */
	private function _writeChartInCell(Generate_PHPExcel_Worksheet $pSheet, $coordinates) {
		// Construct HTML
		$html = '';

		// Write charts
		foreach ($pSheet->getChartCollection() as $chart) {
			if ($chart instanceof Generate_PHPExcel_Chart) {
			    $chartCoordinates = $chart->getTopLeftPosition();
				if ($chartCoordinates['cell'] == $coordinates) {
					$chartFileName = Generate_PHPExcel_Shared_File::sys_get_temp_dir().'/'.uniqid().'.png';
					if (!$chart->render($chartFileName)) {
						return;
					}

					$html .= PHP_EOL;
					$imageDetails = getimagesize($chartFileName);
					if ($fp = fopen($chartFileName,"rb", 0)) {
						$picture = fread($fp,filesize($chartFileName));
						fclose($fp);
						// base64 encode the binary data, then break it
						// into chunks according to RFC 2045 semantics
						$base64 = chunk_split(base64_encode($picture));
						$imageData = 'data:'.$imageDetails['mime'].';base64,' . $base64;

						$html .= '<div style="position: relative;">';
						$html .= '<img style="position: absolute; z-index: 1; left: ' . $chartCoordinates['xOffset'] . 'px; top: ' . $chartCoordinates['yOffset'] . 'px; width: ' . $imageDetails[0] . 'px; height: ' . $imageDetails[1] . 'px;" src="' . $imageData . '" border="0" />' . PHP_EOL;
						$html .= '</div>';

						unlink($chartFileName);
					}
				}
			}
		}

		// Return
		return $html;
	}

	/**
	 * Generate CSS styles
	 *
	 * @param	boolean	$generateSurroundingHTML	Generate surrounding HTML tags? (<style> and </style>)
	 * @return	string
	 * @throws	Generate_PHPExcel_Writer_Exception
	 */
	public function generateStyles($generateSurroundingHTML = true) {
		// Generate_PHPExcel object known?
		if (is_null($this->_Generate_PHPExcel)) {
			throw new Generate_PHPExcel_Writer_Exception('Internal Generate_PHPExcel object not set to an instance of an object.');
		}

		// Build CSS
		$css = $this->buildCSS($generateSurroundingHTML);

		// Construct HTML
		$html = '';

		// Start styles
		if ($generateSurroundingHTML) {
			$html .= '	<style type="text/css">' . PHP_EOL;
			$html .= '	  html { ' . $this->_assembleCSS($css['html']) . ' }' . PHP_EOL;
		}

		// Write all other styles
		foreach ($css as $styleName => $styleDefinition) {
			if ($styleName != 'html') {
				$html .= '	  ' . $styleName . ' { ' . $this->_assembleCSS($styleDefinition) . ' }' . PHP_EOL;
			}
		}

		// End styles
		if ($generateSurroundingHTML) {
			$html .= '	</style>' . PHP_EOL;
		}

		// Return
		return $html;
	}

	/**
	 * Build CSS styles
	 *
	 * @param	boolean	$generateSurroundingHTML	Generate surrounding HTML style? (html { })
	 * @return	array
	 * @throws	Generate_PHPExcel_Writer_Exception
	 */
	public function buildCSS($generateSurroundingHTML = true) {
		// Generate_PHPExcel object known?
		if (is_null($this->_Generate_PHPExcel)) {
			throw new Generate_PHPExcel_Writer_Exception('Internal Generate_PHPExcel object not set to an instance of an object.');
		}

		// Cached?
		if (!is_null($this->_cssStyles)) {
			return $this->_cssStyles;
		}

		// Ensure that spans have been calculated
		if (!$this->_spansAreCalculated) {
			$this->_calculateSpans();
		}

		// Construct CSS
		$css = array();

		// Start styles
		if ($generateSurroundingHTML) {
			// html { }
			$css['html']['font-family']	  = 'Calibri, Arial, Helvetica, sans-serif';
			$css['html']['font-size']		= '11pt';
			$css['html']['background-color'] = 'white';
		}


		// table { }
		$css['table']['border-collapse']  = 'collapse';
	    if (!$this->_isPdf) {
			$css['table']['page-break-after'] = 'always';
		}

		// .gridlines td { }
		$css['.gridlines td']['border'] = '1px dotted black';

		// .b {}
		$css['.b']['text-align'] = 'center'; // BOOL

		// .e {}
		$css['.e']['text-align'] = 'center'; // ERROR

		// .f {}
		$css['.f']['text-align'] = 'right'; // FORMULA

		// .inlineStr {}
		$css['.inlineStr']['text-align'] = 'left'; // INLINE

		// .n {}
		$css['.n']['text-align'] = 'right'; // NUMERIC

		// .s {}
		$css['.s']['text-align'] = 'left'; // STRING

		// Calculate cell style hashes
		foreach ($this->_Generate_PHPExcel->getCellXfCollection() as $index => $style) {
			$css['td.style' . $index] = $this->_createCSSStyle( $style );
		}

		// Fetch sheets
		$sheets = array();
		if (is_null($this->_sheetIndex)) {
			$sheets = $this->_Generate_PHPExcel->getAllSheets();
		} else {
			$sheets[] = $this->_Generate_PHPExcel->getSheet($this->_sheetIndex);
		}

		// Build styles per sheet
		foreach ($sheets as $sheet) {
			// Calculate hash code
			$sheetIndex = $sheet->getParent()->getIndex($sheet);

			// Build styles
			// Calculate column widths
			$sheet->calculateColumnWidths();

			// col elements, initialize
			$highestColumnIndex = Generate_PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn()) - 1;
			$column = -1;
			while($column++ < $highestColumnIndex) {
				$this->_columnWidths[$sheetIndex][$column] = 42; // approximation
				$css['table.sheet' . $sheetIndex . ' col.col' . $column]['width'] = '42pt';
			}

			// col elements, loop through columnDimensions and set width
			foreach ($sheet->getColumnDimensions() as $columnDimension) {
				if (($width = Generate_PHPExcel_Shared_Drawing::cellDimensionToPixels($columnDimension->getWidth(), $this->_defaultFont)) >= 0) {
					$width = Generate_PHPExcel_Shared_Drawing::pixelsToPoints($width);
					$column = Generate_PHPExcel_Cell::columnIndexFromString($columnDimension->getColumnIndex()) - 1;
					$this->_columnWidths[$sheetIndex][$column] = $width;
					$css['table.sheet' . $sheetIndex . ' col.col' . $column]['width'] = $width . 'pt';

					if ($columnDimension->getVisible() === false) {
						$css['table.sheet' . $sheetIndex . ' col.col' . $column]['visibility'] = 'collapse';
						$css['table.sheet' . $sheetIndex . ' col.col' . $column]['*display'] = 'none'; // target IE6+7
					}
				}
			}

			// Default row height
			$rowDimension = $sheet->getDefaultRowDimension();

			// table.sheetN tr { }
			$css['table.sheet' . $sheetIndex . ' tr'] = array();

			if ($rowDimension->getRowHeight() == -1) {
				$pt_height = Generate_PHPExcel_Shared_Font::getDefaultRowHeightByFont($this->_Generate_PHPExcel->getDefaultStyle()->getFont());
			} else {
				$pt_height = $rowDimension->getRowHeight();
			}
			$css['table.sheet' . $sheetIndex . ' tr']['height'] = $pt_height . 'pt';
			if ($rowDimension->getVisible() === false) {
				$css['table.sheet' . $sheetIndex . ' tr']['display']	= 'none';
				$css['table.sheet' . $sheetIndex . ' tr']['visibility'] = 'hidden';
			}

			// Calculate row heights
			foreach ($sheet->getRowDimensions() as $rowDimension) {
				$row = $rowDimension->getRowIndex() - 1;

				// table.sheetN tr.rowYYYYYY { }
				$css['table.sheet' . $sheetIndex . ' tr.row' . $row] = array();

				if ($rowDimension->getRowHeight() == -1) {
					$pt_height = Generate_PHPExcel_Shared_Font::getDefaultRowHeightByFont($this->_Generate_PHPExcel->getDefaultStyle()->getFont());
				} else {
					$pt_height = $rowDimension->getRowHeight();
				}
				$css['table.sheet' . $sheetIndex . ' tr.row' . $row]['height'] = $pt_height . 'pt';
				if ($rowDimension->getVisible() === false) {
					$css['table.sheet' . $sheetIndex . ' tr.row' . $row]['display'] = 'none';
					$css['table.sheet' . $sheetIndex . ' tr.row' . $row]['visibility'] = 'hidden';
				}
			}
		}

		// Cache
		if (is_null($this->_cssStyles)) {
			$this->_cssStyles = $css;
		}

		// Return
		return $css;
	}

	/**
	 * Create CSS style
	 *
	 * @param	Generate_PHPExcel_Style		$pStyle			Generate_PHPExcel_Style
	 * @return	array
	 */
	private function _createCSSStyle(Generate_PHPExcel_Style $pStyle) {
		// Construct CSS
		$css = '';

		// Create CSS
		$css = array_merge(
			$this->_createCSSStyleAlignment($pStyle->getAlignment())
			, $this->_createCSSStyleBorders($pStyle->getBorders())
			, $this->_createCSSStyleFont($pStyle->getFont())
			, $this->_createCSSStyleFill($pStyle->getFill())
		);

		// Return
		return $css;
	}

	/**
	 * Create CSS style (Generate_PHPExcel_Style_Alignment)
	 *
	 * @param	Generate_PHPExcel_Style_Alignment		$pStyle			Generate_PHPExcel_Style_Alignment
	 * @return	array
	 */
	private function _createCSSStyleAlignment(Generate_PHPExcel_Style_Alignment $pStyle) {
		// Construct CSS
		$css = array();

		// Create CSS
		$css['vertical-align'] = $this->_mapVAlign($pStyle->getVertical());
		if ($textAlign = $this->_mapHAlign($pStyle->getHorizontal())) {
			$css['text-align'] = $textAlign;
			if(in_array($textAlign,array('left','right')))
				$css['padding-'.$textAlign] = (string)((int)$pStyle->getIndent() * 9).'px';
		}

		// Return
		return $css;
	}

	/**
	 * Create CSS style (Generate_PHPExcel_Style_Font)
	 *
	 * @param	Generate_PHPExcel_Style_Font		$pStyle			Generate_PHPExcel_Style_Font
	 * @return	array
	 */
	private function _createCSSStyleFont(Generate_PHPExcel_Style_Font $pStyle) {
		// Construct CSS
		$css = array();

		// Create CSS
		if ($pStyle->getBold()) {
			$css['font-weight'] = 'bold';
		}
		if ($pStyle->getUnderline() != Generate_PHPExcel_Style_Font::UNDERLINE_NONE && $pStyle->getStrikethrough()) {
			$css['text-decoration'] = 'underline line-through';
		} else if ($pStyle->getUnderline() != Generate_PHPExcel_Style_Font::UNDERLINE_NONE) {
			$css['text-decoration'] = 'underline';
		} else if ($pStyle->getStrikethrough()) {
			$css['text-decoration'] = 'line-through';
		}
		if ($pStyle->getItalic()) {
			$css['font-style'] = 'italic';
		}

		$css['color']		= '#' . $pStyle->getColor()->getRGB();
		$css['font-family']	= '\'' . $pStyle->getName() . '\'';
		$css['font-size']	= $pStyle->getSize() . 'pt';

		// Return
		return $css;
	}

	/**
	 * Create CSS style (Generate_PHPExcel_Style_Borders)
	 *
	 * @param	Generate_PHPExcel_Style_Borders		$pStyle			Generate_PHPExcel_Style_Borders
	 * @return	array
	 */
	private function _createCSSStyleBorders(Generate_PHPExcel_Style_Borders $pStyle) {
		// Construct CSS
		$css = array();

		// Create CSS
		$css['border-bottom']	= $this->_createCSSStyleBorder($pStyle->getBottom());
		$css['border-top']		= $this->_createCSSStyleBorder($pStyle->getTop());
		$css['border-left']		= $this->_createCSSStyleBorder($pStyle->getLeft());
		$css['border-right']	= $this->_createCSSStyleBorder($pStyle->getRight());

		// Return
		return $css;
	}

	/**
	 * Create CSS style (Generate_PHPExcel_Style_Border)
	 *
	 * @param	Generate_PHPExcel_Style_Border		$pStyle			Generate_PHPExcel_Style_Border
	 * @return	string
	 */
	private function _createCSSStyleBorder(Generate_PHPExcel_Style_Border $pStyle) {
		// Create CSS
//		$css = $this->_mapBorderStyle($pStyle->getBorderStyle()) . ' #' . $pStyle->getColor()->getRGB();
		//	Create CSS - add !important to non-none border styles for merged cells  
		$borderStyle = $this->_mapBorderStyle($pStyle->getBorderStyle());  
		$css = $borderStyle . ' #' . $pStyle->getColor()->getRGB() . (($borderStyle == 'none') ? '' : ' !important'); 

		// Return
		return $css;
	}

	/**
	 * Create CSS style (Generate_PHPExcel_Style_Fill)
	 *
	 * @param	Generate_PHPExcel_Style_Fill		$pStyle			Generate_PHPExcel_Style_Fill
	 * @return	array
	 */
	private function _createCSSStyleFill(Generate_PHPExcel_Style_Fill $pStyle) {
		// Construct HTML
		$css = array();

		// Create CSS
		$value = $pStyle->getFillType() == Generate_PHPExcel_Style_Fill::FILL_NONE ?
			'white' : '#' . $pStyle->getStartColor()->getRGB();
		$css['background-color'] = $value;

		// Return
		return $css;
	}

	/**
	 * Generate HTML footer
	 */
	public function generateHTMLFooter() {
		// Construct HTML
		$html = '';
		$html .= '  </body>' . PHP_EOL;
		$html .= '</html>' . PHP_EOL;

		// Return
		return $html;
	}

	/**
	 * Generate table header
	 *
	 * @param	Generate_PHPExcel_Worksheet	$pSheet		The worksheet for the table we are writing
	 * @return	string
	 * @throws	Generate_PHPExcel_Writer_Exception
	 */
	private function _generateTableHeader($pSheet) {
		$sheetIndex = $pSheet->getParent()->getIndex($pSheet);

		// Construct HTML
		$html = '';
		$html .= $this->_setMargins($pSheet);
			
		if (!$this->_useInlineCss) {
			$gridlines = $pSheet->getShowGridlines() ? ' gridlines' : '';
			$html .= '	<table border="0" cellpadding="0" cellspacing="0" id="sheet' . $sheetIndex . '" class="sheet' . $sheetIndex . $gridlines . '">' . PHP_EOL;
		} else {
			$style = isset($this->_cssStyles['table']) ?
				$this->_assembleCSS($this->_cssStyles['table']) : '';

			if ($this->_isPdf && $pSheet->getShowGridlines()) {
				$html .= '	<table border="1" cellpadding="1" id="sheet' . $sheetIndex . '" cellspacing="1" style="' . $style . '">' . PHP_EOL;
			} else {
				$html .= '	<table border="0" cellpadding="1" id="sheet' . $sheetIndex . '" cellspacing="0" style="' . $style . '">' . PHP_EOL;
			}
		}

		// Write <col> elements
		$highestColumnIndex = Generate_PHPExcel_Cell::columnIndexFromString($pSheet->getHighestColumn()) - 1;
		$i = -1;
		while($i++ < $highestColumnIndex) {
		    if (!$this->_isPdf) {
				if (!$this->_useInlineCss) {
					$html .= '		<col class="col' . $i . '">' . PHP_EOL;
				} else {
					$style = isset($this->_cssStyles['table.sheet' . $sheetIndex . ' col.col' . $i]) ?
						$this->_assembleCSS($this->_cssStyles['table.sheet' . $sheetIndex . ' col.col' . $i]) : '';
					$html .= '		<col style="' . $style . '">' . PHP_EOL;
				}
			}
		}

		// Return
		return $html;
	}

	/**
	 * Generate table footer
	 *
	 * @throws	Generate_PHPExcel_Writer_Exception
	 */
	private function _generateTableFooter() {
		// Construct HTML
		$html = '';
		$html .= '	</table>' . PHP_EOL;

		// Return
		return $html;
	}

	/**
	 * Generate row
	 *
	 * @param	Generate_PHPExcel_Worksheet	$pSheet			Generate_PHPExcel_Worksheet
	 * @param	array				$pValues		Array containing cells in a row
	 * @param	int					$pRow			Row number (0-based)
	 * @return	string
	 * @throws	Generate_PHPExcel_Writer_Exception
	 */
	private function _generateRow(Generate_PHPExcel_Worksheet $pSheet, $pValues = null, $pRow = 0) {
		if (is_array($pValues)) {
			// Construct HTML
			$html = '';

			// Sheet index
			$sheetIndex = $pSheet->getParent()->getIndex($pSheet);

			// DomPDF and breaks
			if ($this->_isPdf && count($pSheet->getBreaks()) > 0) {
				$breaks = $pSheet->getBreaks();

				// check if a break is needed before this row
				if (isset($breaks['A' . $pRow])) {
					// close table: </table>
					$html .= $this->_generateTableFooter();

					// insert page break
					$html .= '<div style="page-break-before:always" />';

					// open table again: <table> + <col> etc.
					$html .= $this->_generateTableHeader($pSheet);
				}
			}

			// Write row start
			if (!$this->_useInlineCss) {
				$html .= '		  <tr class="row' . $pRow . '">' . PHP_EOL;
			} else {
				$style = isset($this->_cssStyles['table.sheet' . $sheetIndex . ' tr.row' . $pRow])
					? $this->_assembleCSS($this->_cssStyles['table.sheet' . $sheetIndex . ' tr.row' . $pRow]) : '';

				$html .= '		  <tr style="' . $style . '">' . PHP_EOL;
			}

			// Write cells
			$colNum = 0;
			foreach ($pValues as $cellAddress) {
                $cell = ($cellAddress > '') ? $pSheet->getCell($cellAddress) : '';
				$coordinate = Generate_PHPExcel_Cell::stringFromColumnIndex($colNum) . ($pRow + 1);
				if (!$this->_useInlineCss) {
					$cssClass = '';
					$cssClass = 'column' . $colNum;
				} else {
					$cssClass = array();
					if (isset($this->_cssStyles['table.sheet' . $sheetIndex . ' td.column' . $colNum])) {
						$this->_cssStyles['table.sheet' . $sheetIndex . ' td.column' . $colNum];
					}
				}
				$colSpan = 1;
				$rowSpan = 1;

				// initialize
				$cellData = '&nbsp;';

				// Generate_PHPExcel_Cell
				if ($cell instanceof Generate_PHPExcel_Cell) {
					$cellData = '';
					if (is_null($cell->getParent())) {
						$cell->attach($pSheet);
					}
					// Value
					if ($cell->getValue() instanceof Generate_PHPExcel_RichText) {
						// Loop through rich text elements
						$elements = $cell->getValue()->getRichTextElements();
						foreach ($elements as $element) {
							// Rich text start?
							if ($element instanceof Generate_PHPExcel_RichText_Run) {
								$cellData .= '<span style="' . $this->_assembleCSS($this->_createCSSStyleFont($element->getFont())) . '">';

								if ($element->getFont()->getSuperScript()) {
									$cellData .= '<sup>';
								} else if ($element->getFont()->getSubScript()) {
									$cellData .= '<sub>';
								}
							}

							// Convert UTF8 data to PCDATA
							$cellText = $element->getText();
							$cellData .= htmlspecialchars($cellText);

							if ($element instanceof Generate_PHPExcel_RichText_Run) {
								if ($element->getFont()->getSuperScript()) {
									$cellData .= '</sup>';
								} else if ($element->getFont()->getSubScript()) {
									$cellData .= '</sub>';
								}

								$cellData .= '</span>';
							}
						}
					} else {
						if ($this->_preCalculateFormulas) {
							$cellData = Generate_PHPExcel_Style_NumberFormat::toFormattedString(
								$cell->getCalculatedValue(),
								$pSheet->getParent()->getCellXfByIndex( $cell->getXfIndex() )->getNumberFormat()->getFormatCode(),
								array($this, 'formatColor')
							);
						} else {
							$cellData = Generate_PHPExcel_Style_NumberFormat::ToFormattedString(
								$cell->getValue(),
								$pSheet->getParent()->getCellXfByIndex( $cell->getXfIndex() )->getNumberFormat()->getFormatCode(),
								array($this, 'formatColor')
							);
						}
						$cellData = htmlspecialchars($cellData);
						if ($pSheet->getParent()->getCellXfByIndex( $cell->getXfIndex() )->getFont()->getSuperScript()) {
							$cellData = '<sup>'.$cellData.'</sup>';
						} elseif ($pSheet->getParent()->getCellXfByIndex( $cell->getXfIndex() )->getFont()->getSubScript()) {
							$cellData = '<sub>'.$cellData.'</sub>';
						}
					}

					// Converts the cell content so that spaces occuring at beginning of each new line are replaced by &nbsp;
					// Example: "  Hello\n to the world" is converted to "&nbsp;&nbsp;Hello\n&nbsp;to the world"
					$cellData = preg_replace("/(?m)(?:^|\\G) /", '&nbsp;', $cellData);

					// convert newline "\n" to '<br>'
					$cellData = nl2br($cellData);

					// Extend CSS class?
					if (!$this->_useInlineCss) {
						$cssClass .= ' style' . $cell->getXfIndex();
						$cssClass .= ' ' . $cell->getDataType();
					} else {
						if (isset($this->_cssStyles['td.style' . $cell->getXfIndex()])) {
							$cssClass = array_merge($cssClass, $this->_cssStyles['td.style' . $cell->getXfIndex()]);
						}

						// General horizontal alignment: Actual horizontal alignment depends on dataType
						$sharedStyle = $pSheet->getParent()->getCellXfByIndex( $cell->getXfIndex() );
						if ($sharedStyle->getAlignment()->getHorizontal() == Generate_PHPExcel_Style_Alignment::HORIZONTAL_GENERAL
							&& isset($this->_cssStyles['.' . $cell->getDataType()]['text-align']))
						{
							$cssClass['text-align'] = $this->_cssStyles['.' . $cell->getDataType()]['text-align'];
						}
					}
				}

				// Hyperlink?
				if ($pSheet->hyperlinkExists($coordinate) && !$pSheet->getHyperlink($coordinate)->isInternal()) {
					$cellData = '<a href="' . htmlspecialchars($pSheet->getHyperlink($coordinate)->getUrl()) . '" title="' . htmlspecialchars($pSheet->getHyperlink($coordinate)->getTooltip()) . '">' . $cellData . '</a>';
				}

				// Should the cell be written or is it swallowed by a rowspan or colspan?
				$writeCell = ! ( isset($this->_isSpannedCell[$pSheet->getParent()->getIndex($pSheet)][$pRow + 1][$colNum])
							&& $this->_isSpannedCell[$pSheet->getParent()->getIndex($pSheet)][$pRow + 1][$colNum] );

				// Colspan and Rowspan
				$colspan = 1;
				$rowspan = 1;
				if (isset($this->_isBaseCell[$pSheet->getParent()->getIndex($pSheet)][$pRow + 1][$colNum])) {
					$spans = $this->_isBaseCell[$pSheet->getParent()->getIndex($pSheet)][$pRow + 1][$colNum];
					$rowSpan = $spans['rowspan'];
					$colSpan = $spans['colspan'];

					//	Also apply style from last cell in merge to fix borders -
					//		relies on !important for non-none border declarations in _createCSSStyleBorder
					$endCellCoord = Generate_PHPExcel_Cell::stringFromColumnIndex($colNum + $colSpan - 1) . ($pRow + $rowSpan);
					if (!$this->_useInlineCss) {
						$cssClass .= ' style' . $pSheet->getCell($endCellCoord)->getXfIndex();
					}
				}

				// Write
				if ($writeCell) {
					// Column start
					$html .= '			<td';
						if (!$this->_useInlineCss) {
							$html .= ' class="' . $cssClass . '"';
						} else {
							//** Necessary redundant code for the sake of Generate_PHPExcel_Writer_PDF **
							// We must explicitly write the width of the <td> element because TCPDF
							// does not recognize e.g. <col style="width:42pt">
							$width = 0;
							$i = $colNum - 1;
							$e = $colNum + $colSpan - 1;
							while($i++ < $e) {
								if (isset($this->_columnWidths[$sheetIndex][$i])) {
									$width += $this->_columnWidths[$sheetIndex][$i];
								}
							}
							$cssClass['width'] = $width . 'pt';

							// We must also explicitly write the height of the <td> element because TCPDF
							// does not recognize e.g. <tr style="height:50pt">
							if (isset($this->_cssStyles['table.sheet' . $sheetIndex . ' tr.row' . $pRow]['height'])) {
								$height = $this->_cssStyles['table.sheet' . $sheetIndex . ' tr.row' . $pRow]['height'];
								$cssClass['height'] = $height;
							}
							//** end of redundant code **

							$html .= ' style="' . $this->_assembleCSS($cssClass) . '"';
						}
						if ($colSpan > 1) {
							$html .= ' colspan="' . $colSpan . '"';
						}
						if ($rowSpan > 1) {
							$html .= ' rowspan="' . $rowSpan . '"';
						}
					$html .= '>';

					// Image?
					$html .= $this->_writeImageInCell($pSheet, $coordinate);

					// Chart?
					if ($this->_includeCharts) {
						$html .= $this->_writeChartInCell($pSheet, $coordinate);
					}

					// Cell data
					$html .= $cellData;

					// Column end
					$html .= '</td>' . PHP_EOL;
				}

				// Next column
				++$colNum;
			}

			// Write row end
			$html .= '		  </tr>' . PHP_EOL;

			// Return
			return $html;
		} else {
			throw new Generate_PHPExcel_Writer_Exception("Invalid parameters passed.");
		}
	}

	/**
	 * Takes array where of CSS properties / values and converts to CSS string
	 *
	 * @param array
	 * @return string
	 */
	private function _assembleCSS($pValue = array())
	{
		$pairs = array();
		foreach ($pValue as $property => $value) {
			$pairs[] = $property . ':' . $value;
		}
		$string = implode('; ', $pairs);

		return $string;
	}

	/**
	 * Get images root
	 *
	 * @return string
	 */
	public function getImagesRoot() {
		return $this->_imagesRoot;
	}

	/**
	 * Set images root
	 *
	 * @param string $pValue
	 * @return Generate_PHPExcel_Writer_HTML
	 */
	public function setImagesRoot($pValue = '.') {
		$this->_imagesRoot = $pValue;
		return $this;
	}

	/**
	 * Get embed images
	 *
	 * @return boolean
	 */
	public function getEmbedImages() {
		return $this->_embedImages;
	}

	/**
	 * Set embed images
	 *
	 * @param boolean $pValue
	 * @return Generate_PHPExcel_Writer_HTML
	 */
	public function setEmbedImages($pValue = '.') {
		$this->_embedImages = $pValue;
		return $this;
	}

	/**
	 * Get use inline CSS?
	 *
	 * @return boolean
	 */
	public function getUseInlineCss() {
		return $this->_useInlineCss;
	}

	/**
	 * Set use inline CSS?
	 *
	 * @param boolean $pValue
	 * @return Generate_PHPExcel_Writer_HTML
	 */
	public function setUseInlineCss($pValue = false) {
		$this->_useInlineCss = $pValue;
		return $this;
	}

	/**
	 * Add color to formatted string as inline style
	 *
	 * @param string $pValue Plain formatted value without color
	 * @param string $pFormat Format code
	 * @return string
	 */
	public function formatColor($pValue, $pFormat)
	{
		// Color information, e.g. [Red] is always at the beginning
		$color = null; // initialize
		$matches = array();

		$color_regex = '/^\\[[a-zA-Z]+\\]/';
		if (preg_match($color_regex, $pFormat, $matches)) {
			$color = str_replace('[', '', $matches[0]);
			$color = str_replace(']', '', $color);
			$color = strtolower($color);
		}

		// convert to PCDATA
		$value = htmlspecialchars($pValue);

		// color span tag
		if ($color !== null) {
			$value = '<span style="color:' . $color . '">' . $value . '</span>';
		}

		return $value;
	}

	/**
	 * Calculate information about HTML colspan and rowspan which is not always the same as Excel's
	 */
	private function _calculateSpans()
	{
		// Identify all cells that should be omitted in HTML due to cell merge.
		// In HTML only the upper-left cell should be written and it should have
		//   appropriate rowspan / colspan attribute
		$sheetIndexes = $this->_sheetIndex !== null ?
			array($this->_sheetIndex) : range(0, $this->_Generate_PHPExcel->getSheetCount() - 1);

		foreach ($sheetIndexes as $sheetIndex) {
			$sheet = $this->_Generate_PHPExcel->getSheet($sheetIndex);

			$candidateSpannedRow  = array();

			// loop through all Excel merged cells
			foreach ($sheet->getMergeCells() as $cells) {
				list($cells, ) = Generate_PHPExcel_Cell::splitRange($cells);
				$first = $cells[0];
				$last  = $cells[1];

				list($fc, $fr) = Generate_PHPExcel_Cell::coordinateFromString($first);
				$fc = Generate_PHPExcel_Cell::columnIndexFromString($fc) - 1;

				list($lc, $lr) = Generate_PHPExcel_Cell::coordinateFromString($last);
				$lc = Generate_PHPExcel_Cell::columnIndexFromString($lc) - 1;

				// loop through the individual cells in the individual merge
				$r = $fr - 1;
				while($r++ < $lr) {
					// also, flag this row as a HTML row that is candidate to be omitted
					$candidateSpannedRow[$r] = $r;

					$c = $fc - 1;
					while($c++ < $lc) {
						if ( !($c == $fc && $r == $fr) ) {
							// not the upper-left cell (should not be written in HTML)
							$this->_isSpannedCell[$sheetIndex][$r][$c] = array(
								'baseCell' => array($fr, $fc),
							);
						} else {
							// upper-left is the base cell that should hold the colspan/rowspan attribute
							$this->_isBaseCell[$sheetIndex][$r][$c] = array(
								'xlrowspan' => $lr - $fr + 1, // Excel rowspan
								'rowspan'   => $lr - $fr + 1, // HTML rowspan, value may change
								'xlcolspan' => $lc - $fc + 1, // Excel colspan
								'colspan'   => $lc - $fc + 1, // HTML colspan, value may change
							);
						}
					}
				}
			}

			// Identify which rows should be omitted in HTML. These are the rows where all the cells
			//   participate in a merge and the where base cells are somewhere above.
			$countColumns = Generate_PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
			foreach ($candidateSpannedRow as $rowIndex) {
				if (isset($this->_isSpannedCell[$sheetIndex][$rowIndex])) {
					if (count($this->_isSpannedCell[$sheetIndex][$rowIndex]) == $countColumns) {
						$this->_isSpannedRow[$sheetIndex][$rowIndex] = $rowIndex;
					};
				}
			}

			// For each of the omitted rows we found above, the affected rowspans should be subtracted by 1
			if ( isset($this->_isSpannedRow[$sheetIndex]) ) {
				foreach ($this->_isSpannedRow[$sheetIndex] as $rowIndex) {
					$adjustedBaseCells = array();
					$c = -1;
					$e = $countColumns - 1;
					while($c++ < $e) {
						$baseCell = $this->_isSpannedCell[$sheetIndex][$rowIndex][$c]['baseCell'];

						if ( !in_array($baseCell, $adjustedBaseCells) ) {
							// subtract rowspan by 1
							--$this->_isBaseCell[$sheetIndex][ $baseCell[0] ][ $baseCell[1] ]['rowspan'];
							$adjustedBaseCells[] = $baseCell;
						}
					}
				}
			}

			// TODO: Same for columns
		}

		// We have calculated the spans
		$this->_spansAreCalculated = true;
	}

	private function _setMargins(Generate_PHPExcel_Worksheet $pSheet) {
		$htmlPage = '@page { ';
		$htmlBody = 'body { ';

		$left = Generate_PHPExcel_Shared_String::FormatNumber($pSheet->getPageMargins()->getLeft()) . 'in; ';
		$htmlPage .= 'left-margin: ' . $left;
		$htmlBody .= 'left-margin: ' . $left;
		$right = Generate_PHPExcel_Shared_String::FormatNumber($pSheet->getPageMargins()->getRight()) . 'in; ';
		$htmlPage .= 'right-margin: ' . $right;
		$htmlBody .= 'right-margin: ' . $right;
		$top = Generate_PHPExcel_Shared_String::FormatNumber($pSheet->getPageMargins()->getTop()) . 'in; ';
		$htmlPage .= 'top-margin: ' . $top;
		$htmlBody .= 'top-margin: ' . $top;
		$bottom = Generate_PHPExcel_Shared_String::FormatNumber($pSheet->getPageMargins()->getBottom()) . 'in; ';
		$htmlPage .= 'bottom-margin: ' . $bottom;
		$htmlBody .= 'bottom-margin: ' . $bottom;

		$htmlPage .= "}\n";
		$htmlBody .= "}\n";

		return "<style>\n" . $htmlPage . $htmlBody . "</style>\n";
	}
	
}
