# start a remote debugging session
```
php -dxdebug.remote_enable=1 \
    -dxdebug.remote_mode=req \
    -dxdebug.remote_port=9000 \
    -dxdebug.remote_host=<ip of host> \
    src/server.php
```