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
class ShortUrlService extends \yii\base\Component{
    const SHORT_URL = 'short_url';
    public $redisConfig = [];
    private $redis = null;

    public function init(){
        $this->redis = new Predis();
        if(!$this->redisConfig){
            throw new InvalidConfigException();
        }
        $config = $this->redisConfig[0];
        $this->redis->connect($config['host'], $config['port']);
        if(!empty($config['password'])){
            $this->redis->auth($config['password']);
        }
        if(!empty($config['database'])){
            $this->redis->select($config['database']);
        }
    }

    /**
     * 保存短连接
     * @param unknown $key
     * @param unknown $value
     * @return int
     */
    public function setUrl($short_tag, $url){
        return $this->redis->hSet(self::SHORT_URL, $short_tag, $url);
    }

    /**
     * 获取短连接
     * @param unknown $short_tag
     */
    public function getUrl($short_tag){
        return $this->redis->hGet(self::SHORT_URL, $short_tag);
    }
}
