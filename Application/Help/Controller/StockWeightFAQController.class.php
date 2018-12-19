<?php
namespace Help\Controller;
use Common\Controller\BaseController;
class StockWeightFAQController extends BaseController
{
    public function escaleFAQ($id=''){
        $this->assign("imageUrl",__ROOT__."/Public/Image/Help/Stock/StockWeight/");
        $this->assign('id',$id);
        $this->display("FAQ");
    }

    public function downloadGetDataSoftware(){
        $file_url = APP_PATH."Runtime/File/Serial.rar";
        downloadFile($file_url);
    }
}