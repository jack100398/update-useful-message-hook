# ReadMe

**install**

```
$ composer config repositories.private-packagist composer https://repo.packagist.com/josh-chen/
$ composer config repositories.packagist.org false
$ composer require update-useful/message-hook
```

in config/app.php providers add
```php
UpdateUseful\MessageHook\MessageHookServiceProvider::class
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
$ cp vendor/update-useful/message-hook/config/MessageHook.php config/CustomMessageHook.php
```

## 設定檔說明

**env**
- 環境設定
- key 代表呼叫時需要的帶的參數
- symbol 代表該環境的tag所會統一使用的符號
- name 為環境名稱

**url**
- 設定 hook 要傳遞的url