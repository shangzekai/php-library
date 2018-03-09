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
 * Generate_PHPExcel_Reader_DefaultReadFilter
 *
 * @category   Generate_PHPExcel
 * @package    Generate_PHPExcel_Reader
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
class Generate_PHPExcel_Reader_DefaultReadFilter implements Generate_PHPExcel_Reader_IReadFilter
{
	/**
	 * Should this cell be read?
	 *
	 * @param 	$column		String column index
	 * @param 	$row			Row index
	 * @param	$worksheetName	Optional worksheet name
	 * @return	boolean
	 */
	public function readCell($column, $row, $worksheetName = '') {
		return true;
	}
}
