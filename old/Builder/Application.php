<?php

namespace Builder;

use Builder\Output\OutputInterface;
use Builder\Input\InputInterface;
use Builder\Input\Input;
use Builder\Output\Output;

class Application
{
    public static $containers;
    private static $autoloader;
    public static $commands;
    private static $instance;
    
    private function __construct() 
    {
        //设置帮助命令
       self::$containers['help'] = 'Builder\\Command\\HelpCommand';
    }
    
    public static function getInstance()
    {
        if(!self::$instance){
            return new self();
        }else{
            return self::$instance;
        }
    }
    /**
     * @params array 
     * 设置命令命名空间
     */
    public static function addCommandNamespace(Array $namespaces = array(), $autoloader = '')
    {
        if(!$autoloader){
            throw new \Exception('请传入正确的composer的自动加载类路径');
        }
        //获取composer自动加载类
        self::$autoloader = $autoloader;
        //注册命令空间
        if (is_array($namespaces)) {
            foreach ($namespaces as $namespace => $dir) {
                $autoloader->setPsr4($namespace, $dir);
                self::register($namespace, $dir);
            }
        }
    }
    
    /**
     * @param type $namespace
     * @param type $dir
     */
    public static function register($namespace, $dir)
    {
        $dirs = scandir($dir);
        foreach ($dirs as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $path = $dir .'/' . $file;
            if(is_dir($path)) {
                self::register($namespace . ucfirst($file) . '\\', $path);
            }
            if(is_file($path)){
                $className = basename($file, '.php');
                $reflectionName = $namespace . $className;
                $reflection = new \ReflectionClass($reflectionName);
                $properties = $reflection->getDefaultProperties();
                self::$containers[$properties['name']] = $reflectionName;
            }
        }
    }
    
    public function run(InputInterface $input, OutputInterface $output)
    {
        //约定命令行第一个参数为命令的名字
        $commandName = $input->get(0);
        
        if(!$commandName || !isset(self::$containers[$commandName]) || $input->hasParameterOption('h')){
            $commandName = 'help';
        }
        
        if (!isset(self::$containers[$commandName])) {
            throw new \Exception('调用的命令不存在！');
        }
        
        $this->execute($commandName, $input, $output);
    }
    
    public function call($commandName, $params = array(), OutputInterface $output = null)
    {
        $input = new Input();
        $input->setArguments($params);
        
        
        if (is_null($output)) {
            $output = new Output();
        }
        
        $this->execute($commandName, $input, $output);
    }
    
    private function execute($commandName, InputInterface $input, OutputInterface $output)
    {
        $commandReflection  = new \ReflectionClass(self::$containers[$commandName]);
        $command            = $commandReflection->newInstance();
        $command->execute($input, $output);
    }
}
