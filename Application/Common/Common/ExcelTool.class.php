<?php
namespace Common\Common;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;

class ExcelTool
{
    protected $filePath ;
    protected $countRows;

    /**
     * 设置文件路径，前提文件路径可以访问
     * @param $file_name
     * @param $file_sub_path
     * @return bool
     */
    public function setFilePath($file_name,$file_sub_path){



        $file_path = $file_sub_path.$file_name;

        //$file_path = iconv('UTF-8','GB2312',$file_path);  //windows

        if(!file_exists($file_path)){
            return false;
        }else{
            $this->filePath=$file_path;
            return $this->filePath;
        }
    }

    //将Excel表格转换为数组
    public function Excel2Arr($count)
    {
        try{
            //调用此方法前需要调用checkExcelFile，uploadFile 方法 来获得file_path
            $filePath = $this->filePath;
            require_once(APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel.php");
            require_once APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel/IOFactory.php";
            require_once APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel/Reader/Excel5.php";
            require_once APP_PATH . "Common/Common/PHPExcelReadFilter.class.php";
            set_time_limit(0);
            $inputFileType =\PHPExcel_IOFactory::identify($filePath);
            $excelReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $excelReader->setReadDataOnly(true);
            $chunkSize = 500;
            $chunkFilter = new \PHPExcelReadFilter();
            $excelReader->setReadDataOnly(true);
            $excelReader->setReadFilter($chunkFilter);
            $objExcel = array();
            $memory = memory_get_peak_usage();
            if($memory>268435456){//内存超出256M
                \Think\Log::write($memory);
                SE('内存超限，请拆分文件导入');
            }
            for($startRow=1;$startRow <=$count;$startRow+=$chunkSize){
                //Log::write('开始-----'.$startRow);
                $chunkFilter->setRows($startRow,$chunkSize);
                $PHPExcel = $excelReader->load($filePath);
                $sheetNames = $PHPExcel->getSheetNames();
                $name         = $sheetNames[0];
                $currentSheet = $PHPExcel->getSheetByName($name);
                $allColumn = $currentSheet->getHighestColumn();
                $allColumn = UtilTool::sumStr($allColumn, "upper");
                $allRow = $currentSheet->getHighestRow();
                for ($currentRow = $startRow; $currentRow <= $allRow; $currentRow++) {
                    for ($currentColumn = 1; $currentColumn <= $allColumn; $currentColumn++) {
                        $val = $currentSheet->getCellByColumnAndRow($currentColumn - 1, $currentRow)->getValue();
                        $val = $val != null ? $val : "";
                        $objExcel[$name][$currentRow - 1][$currentColumn - 1] = html_filter($val);
                        unset($val);
                    }
                }
            }
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
            E($e->getMessage());
        }

        return $objExcel;
    }
     public function checkExcelFile($name, $file){
        //允许文件后缀名
        $allowed_types = array("xls", "xlsx");
        //取得文件名后缀
        $ext = substr(strrchr($name, "."), 1);
        if (!in_array_case($ext, $allowed_types)) {
            unset($file);
            SE("文件名不符合要求，请重新导入");
            return false;
        }
        return true;
    }
    public function uploadFile($file,$str){
        try {
            //获取卖家账号
            $sid = get_sid();
            //移动表格到runtime目录下，该目录定期删除
            $filePath = APP_PATH . "Runtime/File/" . time() . "__" . $sid . "__" . $str . "__" . md5(md5(time()));
            move_uploaded_file($file, $filePath);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            unset($file);
            E("文件错误，无法读取");
            return false;
        }
        $this->filePath=$filePath;
    }
     public function getExcelCount(){
        $filePath=$this->filePath;
        require_once(APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel.php");
        set_time_limit(0);
        $inputFileType =\PHPExcel_IOFactory::identify($filePath);
        $PHPReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $PHPExcel = $PHPReader->load($filePath);
        $activeSheet = $PHPExcel->getActiveSheet();
        $countRows = $activeSheet->getHighestRow();
        return $countRows;
    }
    //删除临时文件
    /*public function delScrathfile(){
        $filepath = $this->filePath;
        unlink($filepath);
    }*/
    /*
     * $arr 要导出的数据,二维数组
     * $title excel sheet的标题
     * $excel_no 数据字段翻译，作为表头标题，一维数组，下标为字段名，值为中文名
     * $width_arr 宽度数组 一维索引数组。跟表头一一对应。
     * $filename 导出的excel名
     * $creator 导出人
     * */
    //将数组转换为Excel表格
    static public function Arr2Excel($arr, $title, $excel_no, $width_arr, $filename, $creator,$merge_arr=array(),$count_arr = array())
    {
        set_time_limit(0);
        //$phpExcel = new PHPExcel();
        if (empty($arr)) {
            $result = array('status' => 1, 'info' => '导出数据为空，请添加！');
            return $result;
        }
        $head = array();
        $head_keys = array();
        foreach($excel_no as $k=>$v){
            $head[]=$v;
            $head_keys[] = $k;
        }
        foreach($arr as $k=>$v){
            foreach($v as $k1=>$v1){
                if(!in_array($k1,$head_keys)){
                    unset($arr[$k][$k1]);
                }
            }
        }
        //返回所有的字段
        $keys_data_arr = array_keys($arr[0]);
        if(!empty($count_arr))
        {
            $keys_data_arr = array_merge($keys_data_arr,array_keys($count_arr));
        }
        //统计有多少个字段
        $count_keys = count($keys_data_arr);
        //excel表头标记
        $excel_sign = array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
            'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
            'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
            'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ',
            'EA', 'EB', 'EC', 'Ed', 'EE', 'EF', 'EG', 'EH', 'EI', 'EJ', 'EK', 'EL', 'EM', 'EN', 'EO', 'EP', 'EQ', 'ER', 'ES', 'ET', 'EU', 'EV', 'EW', 'EX', 'EY', 'EZ',
            'FA', 'FB', 'FC', 'Fd', 'EF', 'FF', 'FG', 'FH', 'FI', 'FJ', 'FK', 'FL', 'FM', 'FN', 'FO', 'FP', 'FQ', 'FR', 'FS', 'FT', 'FU', 'FV', 'FW', 'FX', 'FY', 'FZ'
        );

        require_once APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel.php";
        require_once APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel/IOFactory.php";
        require_once APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel/Reader/Excel5.php";
        require_once APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel/Settings.php";
        require_once APP_PATH . "Common/SDK/PHPExcel/Classes/PHPExcel/CachedObjectStorageFactory.php";

        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array('memoryCacheSize' => '512MB');
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);
        $objPHPExcel = new \PHPExcel();
        //设置属性
        $objPHPExcel->getProperties()->setCreator($creator);
        $objPHPExcel->getProperties()->setTitle($title);
        $objPHPExcel->getProperties()->setSubject('题目');
        //设置宽度 如果没有传设置宽度的数组默认为自动设置宽度
        $objPHPExcel->setActiveSheetIndex(0);
        //自动设置宽度会出问题，尽量不用。自动设置宽度前面2列正常，后面的大部分都会错误。
        if (count($width_arr) == 0) {
            for ($i = 0; $i < $count_keys; $i++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension("$excel_sign[$i]")->setAutoSize(true);
            }
        } else {
            for ($i = 0; $i < $count_keys; $i++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension("$excel_sign[$i]")->setWidth($width_arr[$i]);
            }
        }
        //设置表头
        for ($i = 0; $i < $count_keys; $i++) {
            $indexy = $excel_sign[$i] . '1';
            //$excel_head = empty($excel_no[$keys_data_arr[$i]]) ? $keys_data_arr : $excel_no[$keys_data_arr[$i]];
            $excel_head = $head[$i];
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($indexy, $excel_head);

        }
       //处理需要合并的列。
        if(!empty($merge_arr))
        {
            for ($i = 0; $i < $count_keys; $i++) {
                foreach($merge_arr as $mk=>$mv)
                {
                    if($head_keys[$i] == $mk)
                    {
                        $merge_line[$mk] = $excel_sign[$i];
                    }else{
                        continue;
                    }
                }
            }
            foreach($merge_arr as $k=>$v)
            {
                $line_word = $merge_line[$k];
                $merge_start = 2;
                foreach($v as $k1=>$v1)
                {
                    $merge_end = $merge_start +$v1-1;
                    if($merge_end!=$merge_start)
                    {
                        $merge_list[] = $line_word.$merge_start.":".$line_word.$merge_end;
                    }
                    $merge_start = $merge_end+1;
                }
            }
        }
        //处理需要合并求和的列
        if(!empty($count_arr))
        {
            for ($i = 0; $i < $count_keys; $i++) {
                foreach($count_arr as $mk=>$mv)
                {

                    if($head_keys[$i] == $mk)
                    {
                        $count_merge_line[$mk] = $excel_sign[$i];
                        $count_line[$mk] = $excel_sign[$i-1];
                    }else{
                        continue;
                    }
                }
            }
            $i=0;
            foreach($count_arr as $k=>$v)
            {
                $line_word = $count_merge_line[$k];
                $count_word = $count_line[$k];
                $merge_start = 2;
                foreach($v as $k1=>$v1)
                {
                    $merge_end = $merge_start +$v1-1;
                    if($merge_end!=$merge_start)
                    {
                        $count_list[$i]['merge'] = $line_word.$merge_start.":".$line_word.$merge_end;
                        $count_list[$i]['count'] = $count_word.$merge_start.":".$count_word.$merge_end;
                        $i++;
                    }
                    $merge_start = $merge_end+1;
                }
            }
        };

        //设置值  在设置值的时候设置了该单元格的格式为文本，避免出现科学记数法显示超过9位数字
        foreach ($arr as $k => $v) {
            $j = $k + 2;
            for ($i = 0; $i < $count_keys; $i++) {
                $indexyy = $excel_sign[$i] . $j;
                if(!empty($merge_list))
                {
                    if(in_array($excel_sign[$i],$count_line))
                    {
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($indexyy, '' . $v[$head_keys[$i]], \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    }else{
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($indexyy, '' . $v[$head_keys[$i]], \PHPExcel_Cell_DataType::TYPE_STRING);
                    }
                }else{
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($indexyy, '' . $v[$head_keys[$i]], \PHPExcel_Cell_DataType::TYPE_STRING);

                }
            }
        }
        if(!empty($merge_list))
        {
            foreach($merge_list as $v)
            {
                $objPHPExcel->getActiveSheet()->mergeCells($v);
            }
        }
        if(!empty($count_list))
        {
            foreach($count_list as $v)
            {
                $count_excel_no = substr($v['merge'],0,strpos($v['merge'],':'));
                $objPHPExcel->getActiveSheet()->mergeCells($v['merge']);
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($count_excel_no,'=SUM(' .$v['count'].')');
            }
        }



        $objPHPExcel->getActiveSheet()->setTitle($filename);
        $objPHPExcel->setActiveSheetIndex(0);

        ob_end_clean();//清除缓冲区，避免乱码
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Content-type:test/csv");
        header("Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8");
        $filenames = $filename . '_' . date("Y-m-d", time()) . '_' . time() . '.xls';
        $filenames = iconv('utf-8', 'gb2312', $filenames);
        header("Content-Disposition: attachment;filename={$filenames}");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
    //下载模板
    static public function downloadTemplet($file_name,$file_sub_path){
        header("Content-type:text/html;charset=utf-8");
        //用以解决中文乱码
        //$file_name=iconv("utf-8","gb2312",$file_name);
        $file_path = $file_sub_path.$file_name;
        if(!file_exists($file_path)){
            SE('模板不存在');
        }
        $fp = fopen($file_path,'r');
        $file_size = filesize($file_path);
        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".$file_name);
        $buffer=1024;
        $file_count=0;
        //向浏览器返回数据
        while(!feof($fp) && $file_count<$file_size){
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp);

    }
    /**
     * 导出CSV文件
     * @param array $data        数据
     * @param array $header_data 首行数据
     * @param string $file_name  文件名称
     * @param array $ignore_arr  不去除前置0的列的集合(非数字列)
     * @return string
     */
    static public function Arr2Csv($data, $header_data, $file_name, $ignore_arr=array())
    {
        $file_name = $file_name.".csv";
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $file_name);
        header("Pragma: no-cache");
        header("Expires: 0");
        if (!empty($header_data)) {
            echo iconv('utf-8','gbk//IGNORE','"'.implode('","',$header_data).'"'."\r\n");
        }
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $output = array();
                foreach($header_data as $hd_k => $hd_v){
                    foreach ($v as $ck => $cv) {
                        if($hd_k == $ck){
                            if(in_array($hd_v,$ignore_arr,true)){
                                $cv = "'".$cv;
                            }
                            if(is_numeric($cv) && strlen($cv)>15){
                                $cv = $cv."\t";
                            }
                            $output[] = $cv;
                        }
                    }
                }
                echo iconv('utf-8', 'gbk//IGNORE','"'.implode('","', $output).'"'."\r\n");
            }
        }
    }
}


