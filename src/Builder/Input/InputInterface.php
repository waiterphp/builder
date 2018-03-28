<?php

/* 
 * 输入接口
 */

namespace Builder\Input;

interface InputInterface 
{
    /**
     * 返回第一个除了 - -- 的参数
     */
    function getFirstArgument();
    
    /**
     * 返回去除程序名的 $_SERVER['argv'] 的数据
     */
    function getArguments();
    
    /**
     * 
     * @param String|Int 
     * 
     * @return String 返回要获取的参数
     */
    function getArgument( $name);
    
    /**
     * 判断选项是否存在
     * @param Mix $values 字符串|数组
     * @param bool $onlyParams
     */
    function hasParameterOption($values, $onlyParams = false);
    
    
}
