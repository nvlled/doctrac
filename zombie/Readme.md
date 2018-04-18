

To use these scripts, first install zombieteer, a browser automation tool 
based on puppeteer.js:
```
npm install -g zombieteer
```

Then open browser instance by (do this only once):
```
$ zombieteer --new
```

Sample usages:
```

# login as urd-mis
$ username=urd-mis zombieteer zombie/login.js

# try a dispatch
$ zombieteer zombie/dispatch2.js

```
