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
 * @package    Generate_PHPExcel_Style
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.8.0, 2014-03-02
 */


/**
 * Generate_PHPExcel_Style_Borders
 *
 * @category   Generate_PHPExcel
 * @package    Generate_PHPExcel_Style
 * @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
class Generate_PHPExcel_Style_Borders extends Generate_PHPExcel_Style_Supervisor implements Generate_PHPExcel_IComparable
{
	/* Diagonal directions */
	const DIAGONAL_NONE		= 0;
	const DIAGONAL_UP		= 1;
	const DIAGONAL_DOWN		= 2;
	const DIAGONAL_BOTH		= 3;

	/**
	 * Left
	 *
	 * @var Generate_PHPExcel_Style_Border
	 */
	protected $_left;

	/**
	 * Right
	 *
	 * @var Generate_PHPExcel_Style_Border
	 */
	protected $_right;

	/**
	 * Top
	 *
	 * @var Generate_PHPExcel_Style_Border
	 */
	protected $_top;

	/**
	 * Bottom
	 *
	 * @var Generate_PHPExcel_Style_Border
	 */
	protected $_bottom;

	/**
	 * Diagonal
	 *
	 * @var Generate_PHPExcel_Style_Border
	 */
	protected $_diagonal;

	/**
	 * DiagonalDirection
	 *
	 * @var int
	 */
	protected $_diagonalDirection;

	/**
	 * All borders psedo-border. Only applies to supervisor.
	 *
	 * @var Generate_PHPExcel_Style_Border
	 */
	protected $_allBorders;

	/**
	 * Outline psedo-border. Only applies to supervisor.
	 *
	 * @var Generate_PHPExcel_Style_Border
	 */
	protected $_outline;

	/**
	 * Inside psedo-border. Only applies to supervisor.
	 *
	 * @var Generate_PHPExcel_Style_Border
	 */
	protected $_inside;

	/**
	 * Vertical pseudo-border. Only applies to supervisor.
	 *
	 * @var Generate_PHPExcel_Style_Border
	 */
	protected $_vertical;

	/**
	 * Horizontal pseudo-border. Only applies to supervisor.
	 *
	 * @var Generate_PHPExcel_Style_Border
	 */
	protected $_horizontal;

	/**
     * Create a new Generate_PHPExcel_Style_Borders
	 *
	 * @param	boolean	$isSupervisor	Flag indicating if this is a supervisor or not
	 *									Leave this value at default unless you understand exactly what
	 *										its ramifications are
	 * @param	boolean	$isConditional	Flag indicating if this is a conditional style or not
	 *									Leave this value at default unless you understand exactly what
	 *										its ramifications are
     */
    public function __construct($isSupervisor = FALSE, $isConditional = FALSE)
    {
    	// Supervisor?
		parent::__construct($isSupervisor);

    	// Initialise values
    	$this->_left				= new Generate_PHPExcel_Style_Border($isSupervisor, $isConditional);
    	$this->_right				= new Generate_PHPExcel_Style_Border($isSupervisor, $isConditional);
    	$this->_top					= new Generate_PHPExcel_Style_Border($isSupervisor, $isConditional);
    	$this->_bottom				= new Generate_PHPExcel_Style_Border($isSupervisor, $isConditional);
    	$this->_diagonal			= new Generate_PHPExcel_Style_Border($isSupervisor, $isConditional);
		$this->_diagonalDirection	= Generate_PHPExcel_Style_Borders::DIAGONAL_NONE;

		// Specially for supervisor
		if ($isSupervisor) {
			// Initialize pseudo-borders
			$this->_allBorders			= new Generate_PHPExcel_Style_Border(TRUE);
			$this->_outline				= new Generate_PHPExcel_Style_Border(TRUE);
			$this->_inside				= new Generate_PHPExcel_Style_Border(TRUE);
			$this->_vertical			= new Generate_PHPExcel_Style_Border(TRUE);
			$this->_horizontal			= new Generate_PHPExcel_Style_Border(TRUE);

			// bind parent if we are a supervisor
			$this->_left->bindParent($this, '_left');
			$this->_right->bindParent($this, '_right');
			$this->_top->bindParent($this, '_top');
			$this->_bottom->bindParent($this, '_bottom');
			$this->_diagonal->bindParent($this, '_diagonal');
			$this->_allBorders->bindParent($this, '_allBorders');
			$this->_outline->bindParent($this, '_outline');
			$this->_inside->bindParent($this, '_inside');
			$this->_vertical->bindParent($this, '_vertical');
			$this->_horizontal->bindParent($this, '_horizontal');
		}
    }

	/**
	 * Get the shared style component for the currently active cell in currently active sheet.
	 * Only used for style supervisor
	 *
	 * @return Generate_PHPExcel_Style_Borders
	 */
	public function getSharedComponent()
	{
		return $this->_parent->getSharedComponent()->getBorders();
	}

	/**
	 * Build style array from subcomponents
	 *
	 * @param array $array
	 * @return array
	 */
	public function getStyleArray($array)
	{
		return array('borders' => $array);
	}

	/**
     * Apply styles from array
     *
     * <code>
     * $objGenerate_PHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->applyFromArray(
     * 		array(
     * 			'bottom'     => array(
     * 				'style' => Generate_PHPExcel_Style_Border::BORDER_DASHDOT,
     * 				'color' => array(
     * 					'rgb' => '808080'
     * 				)
     * 			),
     * 			'top'     => array(
     * 				'style' => Generate_PHPExcel_Style_Border::BORDER_DASHDOT,
     * 				'color' => array(
     * 					'rgb' => '808080'
     * 				)
     * 			)
     * 		)
     * );
     * </code>
     * <code>
     * $objGenerate_PHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->applyFromArray(
     * 		array(
     * 			'allborders' => array(
     * 				'style' => Generate_PHPExcel_Style_Border::BORDER_DASHDOT,
     * 				'color' => array(
     * 					'rgb' => '808080'
     * 				)
     * 			)
     * 		)
     * );
     * </code>
     *
     * @param	array	$pStyles	Array containing style information
     * @throws	Generate_PHPExcel_Exception
     * @return Generate_PHPExcel_Style_Borders
     */
	public function applyFromArray($pStyles = null) {
		if (is_array($pStyles)) {
			if ($this->_isSupervisor) {
				$this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($this->getStyleArray($pStyles));
			} else {
				if (array_key_exists('left', $pStyles)) {
					$this->getLeft()->applyFromArray($pStyles['left']);
				}
				if (array_key_exists('right', $pStyles)) {
					$this->getRight()->applyFromArray($pStyles['right']);
				}
				if (array_key_exists('top', $pStyles)) {
					$this->getTop()->applyFromArray($pStyles['top']);
				}
				if (array_key_exists('bottom', $pStyles)) {
					$this->getBottom()->applyFromArray($pStyles['bottom']);
				}
				if (array_key_exists('diagonal', $pStyles)) {
					$this->getDiagonal()->applyFromArray($pStyles['diagonal']);
				}
				if (array_key_exists('diagonaldirection', $pStyles)) {
					$this->setDiagonalDirection($pStyles['diagonaldirection']);
				}
				if (array_key_exists('allborders', $pStyles)) {
					$this->getLeft()->applyFromArray($pStyles['allborders']);
					$this->getRight()->applyFromArray($pStyles['allborders']);
					$this->getTop()->applyFromArray($pStyles['allborders']);
					$this->getBottom()->applyFromArray($pStyles['allborders']);
				}
			}
		} else {
			throw new Generate_PHPExcel_Exception("Invalid style array passed.");
		}
		return $this;
	}

    /**
     * Get Left
     *
     * @return Generate_PHPExcel_Style_Border
     */
    public function getLeft() {
		return $this->_left;
    }

    /**
     * Get Right
     *
     * @return Generate_PHPExcel_Style_Border
     */
    public function getRight() {
		return $this->_right;
    }

    /**
     * Get Top
     *
     * @return Generate_PHPExcel_Style_Border
     */
    public function getTop() {
		return $this->_top;
    }

    /**
     * Get Bottom
     *
     * @return Generate_PHPExcel_Style_Border
     */
    public function getBottom() {
		return $this->_bottom;
    }

    /**
     * Get Diagonal
     *
     * @return Generate_PHPExcel_Style_Border
     */
    public function getDiagonal() {
		return $this->_diagonal;
    }

    /**
     * Get AllBorders (pseudo-border). Only applies to supervisor.
     *
     * @return Generate_PHPExcel_Style_Border
     * @throws Generate_PHPExcel_Exception
     */
    public function getAllBorders() {
		if (!$this->_isSupervisor) {
			throw new Generate_PHPExcel_Exception('Can only get pseudo-border for supervisor.');
		}
		return $this->_allBorders;
    }

    /**
     * Get Outline (pseudo-border). Only applies to supervisor.
     *
     * @return boolean
     * @throws Generate_PHPExcel_Exception
     */
    public function getOutline() {
		if (!$this->_isSupervisor) {
			throw new Generate_PHPExcel_Exception('Can only get pseudo-border for supervisor.');
		}
    	return $this->_outline;
    }

    /**
     * Get Inside (pseudo-border). Only applies to supervisor.
     *
     * @return boolean
     * @throws Generate_PHPExcel_Exception
     */
    public function getInside() {
		if (!$this->_isSupervisor) {
			throw new Generate_PHPExcel_Exception('Can only get pseudo-border for supervisor.');
		}
    	return $this->_inside;
    }

    /**
     * Get Vertical (pseudo-border). Only applies to supervisor.
     *
     * @return Generate_PHPExcel_Style_Border
     * @throws Generate_PHPExcel_Exception
     */
    public function getVertical() {
		if (!$this->_isSupervisor) {
			throw new Generate_PHPExcel_Exception('Can only get pseudo-border for supervisor.');
		}
		return $this->_vertical;
    }

    /**
     * Get Horizontal (pseudo-border). Only applies to supervisor.
     *
     * @return Generate_PHPExcel_Style_Border
     * @throws Generate_PHPExcel_Exception
     */
    public function getHorizontal() {
		if (!$this->_isSupervisor) {
			throw new Generate_PHPExcel_Exception('Can only get pseudo-border for supervisor.');
		}
		return $this->_horizontal;
    }

    /**
     * Get DiagonalDirection
     *
     * @return int
     */
    public function getDiagonalDirection() {
		if ($this->_isSupervisor) {
			return $this->getSharedComponent()->getDiagonalDirection();
		}
    	return $this->_diagonalDirection;
    }

    /**
     * Set DiagonalDirection
     *
     * @param int $pValue
     * @return Generate_PHPExcel_Style_Borders
     */
    public function setDiagonalDirection($pValue = Generate_PHPExcel_Style_Borders::DIAGONAL_NONE) {
        if ($pValue == '') {
    		$pValue = Generate_PHPExcel_Style_Borders::DIAGONAL_NONE;
    	}
		if ($this->_isSupervisor) {
			$styleArray = $this->getStyleArray(array('diagonaldirection' => $pValue));
			$this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
		} else {
			$this->_diagonalDirection = $pValue;
		}
		return $this;
    }

	/**
	 * Get hash code
	 *
	 * @return string	Hash code
	 */
	public function getHashCode() {
		if ($this->_isSupervisor) {
			return $this->getSharedComponent()->getHashcode();
		}
    	return md5(
    		  $this->getLeft()->getHashCode()
    		. $this->getRight()->getHashCode()
    		. $this->getTop()->getHashCode()
    		. $this->getBottom()->getHashCode()
    		. $this->getDiagonal()->getHashCode()
    		. $this->getDiagonalDirection()
    		. __CLASS__
    	);
    }

}
