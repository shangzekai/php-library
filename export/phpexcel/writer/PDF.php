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
 *  @category    Generate_PHPExcel
 *  @package     Generate_PHPExcel_Writer_PDF
 *  @copyright   Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 *  @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 *  @version     1.8.0, 2014-03-02
 */


/**
 *  Generate_PHPExcel_Writer_PDF
 *
 *  @category    Generate_PHPExcel
 *  @package     Generate_PHPExcel_Writer_PDF
 *  @copyright   Copyright (c) 2006 - 2014 Generate_PHPExcel (http://www.codeplex.com/Generate_PHPExcel)
 */
class Generate_PHPExcel_Writer_PDF
{

    /**
     * The wrapper for the requested PDF rendering engine
     *
     * @var Generate_PHPExcel_Writer_PDF_Core
     */
    private $_renderer = NULL;

    /**
     *  Instantiate a new renderer of the configured type within this container class
     *
     *  @param  Generate_PHPExcel   $Generate_PHPExcel         Generate_PHPExcel object
     *  @throws Generate_PHPExcel_Writer_Exception    when PDF library is not configured
     */
    public function __construct(Generate_PHPExcel $Generate_PHPExcel)
    {
        $pdfLibraryName = Generate_PHPExcel_Settings::getPdfRendererName();
        if (is_null($pdfLibraryName)) {
            throw new Generate_PHPExcel_Writer_Exception("PDF Rendering library has not been defined.");
        }

        $pdfLibraryPath = Generate_PHPExcel_Settings::getPdfRendererPath();
        if (is_null($pdfLibraryName)) {
            throw new Generate_PHPExcel_Writer_Exception("PDF Rendering library path has not been defined.");
        }
        $includePath = str_replace('\\', '/', get_include_path());
        $rendererPath = str_replace('\\', '/', $pdfLibraryPath);
        if (strpos($rendererPath, $includePath) === false) {
            set_include_path(get_include_path() . PATH_SEPARATOR . $pdfLibraryPath);
        }

        $rendererName = 'Generate_PHPExcel_Writer_PDF_' . $pdfLibraryName;
        $this->_renderer = new $rendererName($Generate_PHPExcel);
    }


    /**
     *  Magic method to handle direct calls to the configured PDF renderer wrapper class.
     *
     *  @param   string   $name        Renderer library method name
     *  @param   mixed[]  $arguments   Array of arguments to pass to the renderer method
     *  @return  mixed    Returned data from the PDF renderer wrapper method
     */
    public function __call($name, $arguments)
    {
        if ($this->_renderer === NULL) {
            throw new Generate_PHPExcel_Writer_Exception("PDF Rendering library has not been defined.");
        }

        return call_user_func_array(array($this->_renderer, $name), $arguments);
    }

}
