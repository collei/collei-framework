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
Developed currently under Apache Web Server 2.4, PHP 8.0.21, Windows 10.

## Current Features
* MVC based on Route, servlets and views, together with support from database
models, injected services, request filters and, in the future, plugins from
other contributors.
* Database support for MySQL 5+ and MS SQL Server
* Basic plugin management through Packinst 
(see [collei/packinst](https://github.com/collei/packinst) for details) 
* Basic authentication structure with support for Google Authentication

### For the Long Run
* Site Admin Center for site creation, class file management, database profile
management, and so on
* Support for Windows Authentication, Microsoft MFA, and possibly custom auth
mechanisms.
* Support for running under IIS, nginx and other popular web servers

## License
Copyright 2022 Collei <collei@collei.com.br>

For more info, check the LICENSE file.
