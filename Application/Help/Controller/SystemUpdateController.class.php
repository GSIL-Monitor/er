<?php
/**
 * 进行升级相关的PHP类文件.
 *
 * @author gaosong
 * @date 9:40 AM 12/16/15
 *
 */
namespace Help\Controller;
use Common\Controller\BaseController;

class SystemUpdateController extends BaseController
{
    public function getUpdateLog()
    {
        $file = APP_PATH."Help/Controller/update.txt";
        $content = file_get_contents($file) or die("无法显示升级日志请联系管理员!");
        $this->assign('content',$content);
        $this->display('show');
    }

    public function insertUpdateLog()
    {

    }
}

