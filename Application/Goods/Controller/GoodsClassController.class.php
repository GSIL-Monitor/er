<?php
namespace Goods\Controller;

use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;


class GoodsClassController extends BaseController {
    public function getGoodsClassTree() {
        try {
            $tree_arr = D("Goods/GoodsClass")->getTreeClass('all');
            $this->assign('treejson', json_encode($tree_arr));
            $this->display('goods_class_edit');
        } catch (BusinessLogicException $e) {
            $msg = $e->getMessage();
        }catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write("GoodsClassController-getGoodsClassTree-" . $msg);
        }
    }

    public function add($nodeName, $parentNodeId, $isLeaf) {
        $res = array(
            'status' => 0,
            'info'   => "success",
            'data'   => array(),
        );
        $params = I('','',C('JSON_FILTER'));
        try {
            $data = array(
                'class_name' => urldecode($params['nodeName']),
                'parent_id'  => intval($params['parentNodeId']),
                'is_leaf'    => $isLeaf,
            );
            $model = D("Goods/GoodsClass");

            $insert_id = $model->addClass($data);
            //查询路径
            $select_fields = array('path');
            $select_conditons = array(
                'class_id' => array('eq', $parentNodeId),
            );
           
            $parent_info = $model->getOneClass($select_fields,$select_conditons); 
            if(empty($parent_info))
            {
                $parent_path = "-1";
            }
            else {
                $parent_path = $parent_info['path'];
            }
                 
            $path = $parent_path . "," . $insert_id;
            //更新出入数据path
            $update_data = array(
                'path' => $path,
            );
            $update_conditions = array(
                'class_id' => $insert_id,
            );
            $res_update = $model->updateClass($update_data, $update_conditions);
            
        } catch (BusinessLogicException $e) {
            $msg = $e->getMessage();
            $res['status'] = 1;
            $res['info'] = $msg;
        }catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write("GoodsClassController-add-" . $msg);
            $res['status'] = 1;
            $res['info'] = $msg;
        }
        $res['data'][0] = array(
            'class_id' => $insert_id,
        );
        $this->ajaxReturn($res);
        
    }

    /**
     * 函数用途描述
     * @date: 2015年6月25日
     * @author: ct
     * @return:
     */
    public function modify($nodeName, $nodeId) {
        $res = array(
            'status' => 0,
            'info'   => 'success',
            'data'   => array(),
        );
        try {
            $model = D('Goods/GoodsClass');
            $params = I('','',C('JSON_FILTER'));
            $data = array(
                'class_name' => urldecode($params['nodeName']),
            );
            $conditions = array(
                'class_id' => intval($params['nodeId']),
            );

            $res_modify = $model->updateClass($data, $conditions);
        } catch (BusinessLogicException $e) {
            $msg = $e->getMessage();
            $res['status'] = 1;
            $res['info'] = $msg;
        }catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write("GoodsClassController-add-" . $msg);
            $res['status'] = 1;
            $res['info'] = $msg;
        }
        $res['data'][0] = array(
            'class_id' => intval($params['nodeId']),
        );
        $this->ajaxReturn($res);
    }

    /**
     * 函数用途描述
     * @date: 2015年6月25日
     * @author: ct
     * @return:
     */
    public function remove($nodeId) {
        $res = array(
            'status' => 0,
            'info'   => 'success',
            'data'   => array(),
        );
        try {
            $params = I('','',C('JSON_FILTER'));;
            $model = D('Goods/GoodsClass');
            $conditions = array(
                'class_id' => intval($params['nodeId']),
            );

            
            $res_remove = $model->deleteClass($conditions);
            if($res_remove===false || $res_remove ===0)
            {
                $res['status'] = 1;
                $res['info'] = '删除失败';
            }
        } catch (BusinessLogicException $e) {
            $msg = $e->getMessage();
            $res['status'] = 1;
            $res['info'] = $msg;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write("GoodsClassController-remover-" . $msg);
            $res['status'] = 1;
            $res['info'] = self::UNKNOWN_ERROR;
        }
        
        $this->ajaxReturn($res);
    }

    /**
     * 返回货品分类的数据
     * @param $type
     */
    public function getTreeClass($type) {
        try {
            $res = D("Goods/GoodsClass")->getTreeClass($type);
        } catch (BusinessLogicException $e) {
            $res = array();
        } catch (\Exception $e) {
            \Think\Log::write("GoodsClassController--getTreeClass" . $e->getMessage());
            $res = array();
        }
        $this->ajaxReturn($res);
    }

}
  