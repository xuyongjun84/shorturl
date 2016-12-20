<?php

namespace wanpinghui\shorturl\service;

use Yii;
use Predis\Client as Predis;
use yii\base\InvalidConfigException;

interface IRedis{
    public function get($key);
    public function set($key, $value);
}
/**
 * 短链接存放
 * @author peter
 *
 */
class Redis extends \yii\base\Component implements IRedis {
    public $shortTagPrefix = 'st:';
    public $redisConfig = [];
    private $redis = null;

    public function init(){
        if(!$this->redisConfig){
            throw new InvalidConfigException();
        }
        $config = $this->redisConfig;
        if(empty($config['password'])){
            unset($config['password']);
        }
        $this->redis = new Predis($config);
    }

    /**
     * 保存短连接
     * @param unknown $key
     * @param unknown $value
     * @return int
     */
    public function set($short_tag, $url){
        $result = 0;
        try{
            $result = $this->redis->set($this->shortTagPrefix.$short_tag, $url);
            if(isset($this->redisConfig['expire'])){
                $this->redis->expire($this->shortTagPrefix.$short_tag, $this->redisConfig['expire']);
            }
        }catch (\Exception $e){
            Yii::error($e->getMessage(), __METHOD__);
        }
        return $result;
    }

    /**
     * 根据 $short_tag 获取 url
     * @param unknown $short_tag
     */
    public function get($short_tag){
        $value = '';
        try{
            $value = $this->redis->get($this->shortTagPrefix.$short_tag);
            if($value && isset($this->redisConfig['expire'])){
                $this->redis->expire($this->shortTagPrefix.$short_tag, $this->redisConfig['expire']);
            }
        }catch (\Exception $e){
            Yii::error($e->getMessage(), __METHOD__);
        }
        return $value;
    }
}
