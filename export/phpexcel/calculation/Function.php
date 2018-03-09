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
 * @package    Generate_PHPExcel_Calculation
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.8.0, 2014-03-02
 */


/**
 * Generate_PHPExcel_Calculation_Function
 *
 * @category   Generate_PHPExcel
 * @package    Generate_PHPExcel_Calculation
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
class Generate_PHPExcel_Calculation_Function {
	/* Function categories */
	const CATEGORY_CUBE						= 'Cube';
	const CATEGORY_DATABASE					= 'Database';
	const CATEGORY_DATE_AND_TIME			= 'Date and Time';
	const CATEGORY_ENGINEERING				= 'Engineering';
	const CATEGORY_FINANCIAL				= 'Financial';
	const CATEGORY_INFORMATION				= 'Information';
	const CATEGORY_LOGICAL					= 'Logical';
	const CATEGORY_LOOKUP_AND_REFERENCE		= 'Lookup and Reference';
	const CATEGORY_MATH_AND_TRIG			= 'Math and Trig';
	const CATEGORY_STATISTICAL				= 'Statistical';
	const CATEGORY_TEXT_AND_DATA			= 'Text and Data';

	/**
	 * Category (represented by CATEGORY_*)
	 *
	 * @var string
	 */
	private $_category;

	/**
	 * Excel name
	 *
	 * @var string
	 */
	private $_excelName;

	/**
	 * Generate_PHPExcel name
	 *
	 * @var string
	 */
	private $_Generate_PHPExcelName;

    /**
     * Create a new Generate_PHPExcel_Calculation_Function
     *
     * @param 	string		$pCategory 		Category (represented by CATEGORY_*)
     * @param 	string		$pExcelName		Excel function name
     * @param 	string		$pGenerate_PHPExcelName	Generate_PHPExcel function mapping
     * @throws 	Generate_PHPExcel_Calculation_Exception
     */
    public function __construct($pCategory = NULL, $pExcelName = NULL, $pGenerate_PHPExcelName = NULL)
    {
    	if (($pCategory !== NULL) && ($pExcelName !== NULL) && ($pGenerate_PHPExcelName !== NULL)) {
    		// Initialise values
    		$this->_category 		= $pCategory;
    		$this->_excelName 		= $pExcelName;
    		$this->_Generate_PHPExcelName 	= $pGenerate_PHPExcelName;
    	} else {
    		throw new Generate_PHPExcel_Calculation_Exception("Invalid parameters passed.");
    	}
    }

    /**
     * Get Category (represented by CATEGORY_*)
     *
     * @return string
     */
    public function getCategory() {
    	return $this->_category;
    }

    /**
     * Set Category (represented by CATEGORY_*)
     *
     * @param 	string		$value
     * @throws 	Generate_PHPExcel_Calculation_Exception
     */
    public function setCategory($value = null) {
    	if (!is_null($value)) {
    		$this->_category = $value;
    	} else {
    		throw new Generate_PHPExcel_Calculation_Exception("Invalid parameter passed.");
    	}
    }

    /**
     * Get Excel name
     *
     * @return string
     */
    public function getExcelName() {
    	return $this->_excelName;
    }

    /**
     * Set Excel name
     *
     * @param string	$value
     */
    public function setExcelName($value) {
    	$this->_excelName = $value;
    }

    /**
     * Get Generate_PHPExcel name
     *
     * @return string
     */
    public function getGenerate_PHPExcelName() {
    	return $this->_Generate_PHPExcelName;
    }

    /**
     * Set Generate_PHPExcel name
     *
     * @param string	$value
     */
    public function setGenerate_PHPExcelName($value) {
    	$this->_Generate_PHPExcelName = $value;
    }
}
