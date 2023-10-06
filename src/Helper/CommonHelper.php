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
}
