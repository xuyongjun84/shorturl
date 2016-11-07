<?php
namespace wanpinghui\shorturl;

class Module extends \yii\base\Module
{
    public $defaultRoute = 'short-url'; // 即：ShortUrl
    public $long_url = '';
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}