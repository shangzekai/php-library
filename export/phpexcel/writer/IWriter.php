<?php
/**
 *  Generate_PHPExcel
 *
 *  Copyright (c) 2006 - 2014 Generate_PHPExcel
 *
 *  This library is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU Lesser General Public
 *  License as published by the Free Software Foundation; either
 *  version 2.1 of the License, or (at your option) any later version.
 *
 *  This library is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *  Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public
 *  License along with this library; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *  @category   Generate_PHPExcel
 *  @package    Generate_PHPExcel_Writer
 *  @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 *  @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 *  @version    1.8.0, 2014-03-02
 */


/**
 *  Generate_PHPExcel_Writer_IWriter
 *
 *  @category   Generate_PHPExcel
 *  @package    Generate_PHPExcel_Writer
 *  @copyright  Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
interface Generate_PHPExcel_Writer_IWriter
{
    /**
     *  Save Generate_PHPExcel to file
     *
     *  @param   string       $pFilename  Name of the file to save
     *  @throws  Generate_PHPExcel_Writer_Exception
     */
    public function save($pFilename = NULL);

}
