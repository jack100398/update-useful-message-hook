<?php

namespace XinYin\UpgradeTool\Helper;

use Illuminate\Support\Facades\Config;

class CommonHelper
{
    /**
     * 獲得環境設定參數
     *
     * @return array
     */
    public static function getEnvSettings(): array
    {
        return Config::get('custommessagehook.env', Config::get('MessageHook.env'));
    }

    /**
     * 獲得 hook url 設定參數
     */
    public static function getUrlSettings(): string
    {
        return Config::get('custommessagehook.url', Config::get('MessageHook.url'));
    }

    /**
     * 獲得 討論串設定參數
     */
    public static function getThreadSettings(): string
    {
        return Config::get('custommessagehook.thread_key', Config::get('MessageHook.thread_key'));
    }

    /**
     * 獲得 是否使用討論串參數
     */
    public static function getShouldThreadSettings(): string
    {
        return Config::get('custommessagehook.should_thread', Config::get('MessageHook.should_thread'));
    }
}
