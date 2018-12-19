<?php
namespace Common\Common;

class Field
{
    public function getFields($key)
    {
        $fields = $this->get(strtolower($key));
		if (isset($fields)){
			return $fields;
		}else{
			\Think\Log::write('unknown field name:'.$key);
			return array();
		}
    }
    
    protected function get($key)
    {
    	return array();
    }
}