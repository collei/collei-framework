# Collei Plat
A simple MVC framework designed to maintain multiple sites into a single domain.
Inspired on Laravel, Collei Plat aims to support several sites under a single
domain.

## How to Use

### Starting dev
1. Set up a working web server environment (preferably Apache 2.4) with PHP 7.4
or higher.
2. Clone this repo into a folder into that server.
3. Run `composer update` and `composer dump-autoloader`. 
4. Create (or update your existing) .htaccess file and add the following lines:
```
# do not forget turing the engine on
RewriteEngine on
RewriteCond %{REQUEST_URI} !-f 
RewriteCond %{REQUEST_URI} !-d
RewriteRule ^sites(.*)$ /collei-framework/public/index.php [L]
```

Now, you are ready to start working on your multi-site.

### Note on IIS Systems
Just discovered how to run Collei framework under IIS! You must add these
lines to your <Rewrite> section of your Web.config:
```
<rules>
	<rule name="Collei Framework Sites">
		<match url="^sites/(.*)" />
		<action type="Rewrite" url="/collei-framework/public/index.php" />
	</rule>
</rules>
```
Updating the file should work immediately. If it doesn't, just restart the IIS
server.

## Build Status
Developed currently under Apache Web Server 2.4, PHP 8.0.21, Windows 10.

## Current Features
* MVC based on Route, servlets and views, together with support from database
models, injected services, request filters and, in the future, plugins from
other contributors.
* Database support for MySQL 5+ and MS SQL Server
* Basic authentication structure with support for Google Authentication

### For the Long Run
* LDAP-based automatic logon
* Support for Windows Authentication, Microsoft MFA, and possibly custom auth
mechanisms.
* Support for running under IIS, nginx and other popular web servers

## License
Copyright 2022 Collei <collei@collei.com.br>

For more info, check the LICENSE file.
