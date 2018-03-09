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
 * @package    Generate_PHPExcel_CachedObjectStorage
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.8.0, 2014-03-02
 */


/**
 * Generate_PHPExcel_CachedObjectStorage_Memory
 *
 * @category   Generate_PHPExcel
 * @package    Generate_PHPExcel_CachedObjectStorage
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
class Generate_PHPExcel_CachedObjectStorage_Memory extends Generate_PHPExcel_CachedObjectStorage_CacheBase implements Generate_PHPExcel_CachedObjectStorage_ICache {

    /**
     * Dummy method callable from CacheBase, but unused by Memory cache
     *
	 * @return	void
     */
	protected function _storeData() {
	}	//	function _storeData()

    /**
     * Add or Update a cell in cache identified by coordinate address
     *
     * @param	string			$pCoord		Coordinate address of the cell to update
     * @param	Generate_PHPExcel_Cell	$cell		Cell to update
	 * @return	Generate_PHPExcel_Cell
     * @throws	Generate_PHPExcel_Exception
     */
	public function addCacheData($pCoord, Generate_PHPExcel_Cell $cell) {
		$this->_cellCache[$pCoord] = $cell;

		//	Set current entry to the new/updated entry
		$this->_currentObjectID = $pCoord;

		return $cell;
	}	//	function addCacheData()


    /**
     * Get cell at a specific coordinate
     *
     * @param 	string 			$pCoord		Coordinate of the cell
     * @throws 	Generate_PHPExcel_Exception
     * @return 	Generate_PHPExcel_Cell 	Cell that was found, or null if not found
     */
	public function getCacheData($pCoord) {
		//	Check if the entry that has been requested actually exists
		if (!isset($this->_cellCache[$pCoord])) {
			$this->_currentObjectID = NULL;
			//	Return null if requested entry doesn't exist in cache
			return null;
		}

		//	Set current entry to the requested entry
		$this->_currentObjectID = $pCoord;

		//	Return requested entry
		return $this->_cellCache[$pCoord];
	}	//	function getCacheData()


	/**
	 * Clone the cell collection
	 *
	 * @param	Generate_PHPExcel_Worksheet	$parent		The new worksheet
	 * @return	void
	 */
	public function copyCellCollection(Generate_PHPExcel_Worksheet $parent) {
		parent::copyCellCollection($parent);

		$newCollection = array();
		foreach($this->_cellCache as $k => &$cell) {
			$newCollection[$k] = clone $cell;
			$newCollection[$k]->attach($this);
		}

		$this->_cellCache = $newCollection;
	}


	/**
	 * Clear the cell collection and disconnect from our parent
	 *
	 * @return	void
	 */
	public function unsetWorksheetCells() {
		//	Because cells are all stored as intact objects in memory, we need to detach each one from the parent
		foreach($this->_cellCache as $k => &$cell) {
			$cell->detach();
			$this->_cellCache[$k] = null;
		}
		unset($cell);

		$this->_cellCache = array();

		//	detach ourself from the worksheet, so that it can then delete this object successfully
		$this->_parent = null;
	}	//	function unsetWorksheetCells()

}
