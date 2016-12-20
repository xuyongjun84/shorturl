<?php
namespace wanpinghui\shorturl;

class Module extends \yii\base\Module
{
    public $defaultRoute = 'short-url'; // 即导向 ShortUrlController 控制器；yii只允许小写；
    public $long_url = '';
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}