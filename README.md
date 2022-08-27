# Collei Plat
A simple MVC framework designed to maintain multiple sites into a single domain.
Inspired on Laravel, Collei Plat aims to support several sites under a single
domain.

## How to Use
1. Set up a working web server environment (preferably Apache 2.4) with PHP 7.4
or higher.
2. Create (or update your existing) .htaccess file and add the following lines:
```
# do not forget turing the engine on
RewriteEngine on
RewriteCond %{REQUEST_URI} !-f 
RewriteCond %{REQUEST_URI} !-d
RewriteRule ^sites(.*)$ /plat/public/index.php [L]
```

## Build Status
Currently in active developement with several basic features being created and
improved in a daily basis.
Developed currently under Apache Web Server 2.4, PHP 7.4, Windows 10 Pro.

## Current Features
* MVC based on Route, servlets and views, together with support from database
models, injected services, request filters and, in the future, plugins from
other contributors.
* Database support for MySQL 5+ and MS SQL Server
* Basic plugin management through Packinst 
(see [collei/packinst](https://github.com/collei/packinst) for details) 
* Basic authentication structure

### For the Long Run
* Site Admin Center for site creation, class file management, database profile
management, and so on
* Support for Windows Authentication, oAuth, Microsoft, and possibly custom auth
mechanisms.
* Support for running under IIS, nginx and other popular web servers

## License
Copyright 2022 Collei <collei@collei.com.br>

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in the
Software without restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

