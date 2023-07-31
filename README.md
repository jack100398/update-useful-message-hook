# ReadMe

**呼叫指令**
```
$ php artisan update develop
$ php artisan update staging
$ php artisan update cron
$ php artisan update master
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