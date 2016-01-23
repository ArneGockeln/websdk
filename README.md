WebSDK
=========

Mini PHP Source Development Kit to start new projects quickly!

Features
--------
- OOP
- Twitter Bootstrap Frontend
- PHP5/MySQL5 Backend
- Predefined user management
- Predefined right management
- Predefined session management
- Slim Routing Framework
- Twig Template Engine
- Ajax/xhr ready
- GetText Multilanguage Support
- German & English integrated (.po files available for easy translation)

Installation
------------
1. add new mysql database and import install/websdk_starter.sql
2. edit config.php and set database credentials
3. upload files to your webserver (don't forget the .htaccess)
4. login with user 'admin' and password 'Password123'

.htaccess Example
-----------------
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
```

Credits
-------
- Twitter Bootstrap http://getbootstrap.com/
- jQuery http://jquery.com/
- Fontawesome https://fortawesome.github.io/Font-Awesome/
- Glyphicons http://glyphicons.com/
- GNU Gettext http://www.gnu.org/software/gettext/
- PoEdit Mac http://www.poedit.net/
