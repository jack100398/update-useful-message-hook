# ReadMe

**install**

```
$ composer require 168tech/cicd-status-publisher
```

in `.env` file add
```
UPGRADE_HOOK_URL="https://your.hook.url"
```

**呼叫指令**
```
$ php artisan upgrade develop
$ php artisan upgrade staging
$ php artisan upgrade cron
$ php artisan upgrade master
```

**客製化設定檔**
```
$ php artisan vendor:publish --provider="XinYin\upgrade-tool\MessageHookServiceProvider" --tag="config"
```

## 設定檔說明

**env**
- 環境設定
- key 代表呼叫時需要的帶的參數
- symbol 代表該環境的tag所會統一使用的符號
- name 為環境名稱

**url**
- 設定 hook 要傳遞的url