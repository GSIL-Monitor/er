<?php
namespace Goods\Model;

use Think\Model;
use Think\Model\RelationModel;
use Common\Common\UtilTool;

class GoodsClassModel extends RelationModel
{
    protected $tableName = 'goods_class';
    protected $pk = 'class_id';
    protected $_link = array(
        'checkchildren'=>array(
            'mapping_type'=>self::HAS_MANY,
            'class_name'=>'GoodsClass',
            'foreign_key'=>'class_id',
            'parent_key'=>'parent_id',
            'mapping_fields'=>'class_id',
            
        ),
//        'checkGoodsUse'=>array(
//            'mapping_type'=>self::HAS_MANY,
//            'class_name'=>'GoodsGoods',
//            'foreign_key'=>'class_id',
//            'mapping_limit'=>1,
//
//        ),
        'checkStockSyncRule'=>array(
            'mapping_type'=>self::HAS_MANY,
            'class_name'=>'CfgStockSyncRule',
            'foreign_key'=>'class_id',
            'mapping_fields'=>'rule_no',
            
        ),
    );
    protected $relationMapError = array(
        'checkchildren'         =>'当前分类含有子节点，不能删除',
//        'checkGoodsUse'         =>'货品正在使用当前分类，不能删除',
        'checkStockSyncRule'    =>'【库存同步规则】正在使用当前分类，不能删除',
    );
    protected $_validate = array(
        array('parent_id','require','相应父节点信息不存在'),
        array('is_leaf',array(0,1),'节点类型不正确',2,'in'),
        array('is_leaf','number','分类的类型不正确',0,'regex'),
        array('class_name','','货品分类已存在',0,'unique',1),
        array('class_id',array(-1,0),'当前节点不能经行修改和删除操作',2,'notin'),
        array('class_name','checkName','只能输入英文、中文、数字、_、-',0,'callback'),
        //array('path',),
    );
    protected  function checkName($class_name)
    {
        return check_regex('english_chinese',$class_name);
    } 
    public function addClass($data)
    {
        try {
            if (!$this->create($data)) {
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                SE($this->getError());
            }
            $last_id = $this->add($data);
            
            return $last_id;
        }catch (\PDOException $e)
        {
            $msg = $e->getMessage();
            \Think\Log::write('goodsclassmodel-add-'.$msg);
            SE(self::PDO_ERROR);
        }
        

    }
    public function getOneClass($fields, $conditions)
    {
        try {
            $class_info = $this->field($fields)->where($conditions)->find();
            return $class_info;
        }catch(\PDOException $e)
        {
            $msg = $e->getMessage();
            \Think\Log::write('goodsclassmodel-getOneClass-'.$msg);
            SE(self::PDO_ERROR);
        }
        
    }
    public function updateClass($data,$conditions)
    {
        try {
            if (!$this->create(array_merge($data, $conditions))) {
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                SE($this->getError());
            }
            $res_update = $this->where($conditions)->save($data);
            return $res_update;
        }catch(\PDOException $e)
        {
            $msg = $e->getMessage();
            \Think\Log::write('goodsclassmodel-updateClass-'.$msg);
            SE(self::PDO_ERROR);
        }
        
    }
    public function deleteClass($conditions)
    {
        try {
            if (!$this->create($conditions)) {
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                SE($this->getError());
            }
            $astrict = $conditions;
            
            //判断是否含有相应的字节点
            foreach ($this->_link as $key => $val)
            {
                $result = $conditions;
                $this->getRelation($result,$key);
                if(!empty($result["{$key}"]))
                {
                    SE($this->relationMapError[$key]);
                }
                
            }

            $goods_num_in_class= M("goods_goods")->field("COUNT(`goods_id`) AS total")->where("`class_id`=%d and `deleted`=%d",array($conditions['class_id'],0))->select()[0]['total'];


            if($goods_num_in_class > 0){
                SE('货品正在使用当前分类，不能删除');
            }

            $where=array(
                'class_id'=>$conditions['class_id'],
                'delete'=>array('neq',0)
            );

            $res = M("goods_goods")->where($where)->delete();

            $res_delete = $this->where($conditions)->delete();

            return $res_delete;
        }catch(\PDOException $e)
        {
            $msg = $e->getMessage();
            \Think\Log::write('goodsclassmodel-deleteClass-'.$msg);
            SE(self::PDO_ERROR);
        }
        
    }
    /**
     * 返回分类数据
     * @param $type
     * @return array
     */
    public function getTreeClass($type) {
        try {
            $tree_class = $this->field('class_id as id,parent_id,is_leaf as attributes,class_name as text')->order('parent_id,class_id')->select();
            $tree_arr = UtilTool::array2tree($tree_class, 'id', 'parent_id', 'children');
            if ($type == 'all') {
                $tree_arr1[] = array('id' => -1, 'parent_id' => -2, 'attributes' => 0, 'text' => '全部', 'children' => $tree_arr);
                return $tree_arr1;
            }
            return $tree_arr;
        } catch (\Exception $e) {
            \Think\Log::write("GoodsClassModel--getTreeClass:" . $e->getMessage());
            SE("未知错误，请联系管理员");
        }
    }
}

?>