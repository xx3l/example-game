## Install
1. `git clone`
2. `composer install`
3. Change the configuration `Web/config/config_local.json` for the current server ip address or domain name

### Linux
Start in debug mode ```php start.php start``` <br>
Start in daemon mode ```php start.php start -d```  <br>
Check status ```php start.php status```   <br>
Stop ```php start.php stop```  <br>


### Windows
```php start_web.php start_worker.php```