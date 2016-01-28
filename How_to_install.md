1. download new rev from svn
```
svn checkout http://ascent-phpstats.googlecode.com/svn/trunk/
```

2. rename (**config.multi\_server.php** or **config.one\_server.php**) to **config.php**

3. change chmod to 777 http://en.wikipedia.org/wiki/Chmod
> on folders:
    * .\Cache
    * .\Cache\ban
    * .\Cache\cd\_unstuck
    * .\Cache\ch\_pass\_lock
    * .\Cache\MySQL
    * .\Cache\sid
    * .\Cache\XML

4. edit **config.php**