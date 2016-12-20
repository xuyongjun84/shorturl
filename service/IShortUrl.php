<?php

namespace wanpinghui\shorturl\service;

interface IShortUrl{
    public function getUrl($short_tag);
    public function getShortTag($url);
}