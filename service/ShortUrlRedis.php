<?php

namespace wanpinghui\shorturl\service;

use Yii;
use Predis\Client as Predis;
use yii\base\InvalidConfigException;
/**
 * 短链接存放
 * @author peter
 *
 */
class ShortUrlRedis extends \yii\base\Component{
    const SHORT_URL_PRE = 'st:';
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
    public function setUrl($short_tag, $url){
        $result = 0;
        try{
            $result = $this->redis->set(self::SHORT_URL_PRE.$short_tag, $url);
            if(isset($this->redisConfig['expire'])){
                $this->redis->expire(self::SHORT_URL_PRE.$short_tag, $this->redisConfig['expire']);
            }
        }catch (\Exception $e){
            Yii::error($e->getMessage(), __METHOD__);
        }
        return $result;
    }

    /**
     * 获取短连接
     * @param unknown $short_tag
     */
    public function getUrl($short_tag){
        $value = '';
        try{
            $value = $this->redis->get(self::SHORT_URL_PRE.$short_tag);
            if($value && isset($this->redisConfig['expire'])){
                $this->redis->expire(self::SHORT_URL_PRE.$short_tag, $this->redisConfig['expire']);
            }
        }catch (\Exception $e){
            Yii::error($e->getMessage(), __METHOD__);
        }
        return $value;
    }
}
