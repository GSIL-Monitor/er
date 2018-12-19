<?php
require_once(APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel.php");
class PHPExcelReadFilter implements PHPExcel_Reader_IReadFilter {

    private $_startRow = 0;
    private $_endRow=0;
    public function setRows($startRow,$chunkSize){
        $this->_startRow = $startRow;
        $this->_endRow = $startRow + $chunkSize;
    }

    public function readCell($column, $row, $worksheetName = '') {
        if($row==1 || ($row>$this->_startRow && $row<$this->_endRow)){
            return true;
        }
        return false;
    }

}