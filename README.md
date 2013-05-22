More Domains for WordPress
==========================

This plugin allows WordPress installation to run on multiple domains other than installed. Just activate the plugin and head to the second domain pointing to your installation.

Why
---

As a designer/developer you prepare site for some domain. You can of course [setup virtual host on your local server](http://httpd.apache.org/docs/2.2/vhosts/examples.html "VirtualHost Examples") but sometimes you need work both on the live site and your virtual one.

Setup
-----

Lets have a virtual host setup.

### 1. Edit you server setup (MAMP on OS X example) and restart

```
#/Applications/MAMP/conf/apache/extra/httpd-vhosts.conf
<VirtualHost *:80>
	ServerName example.com
	ServerAlias www.example.com
	DocumentRoot /Users/someuser/Sites/example.com/
	<Directory /Users/someuser/Sites/example.com/>
		Options Indexes FollowSymLinks MultiViews
		AllowOverride All
		Order allow,deny
		Allow from all
	</Directory>
</VirtualHost>
```

1.  Local website now can run in `/Users/someuser/Sites/example.com/` directory
2.  Website can be accessed on 2 domains:
	- example.com
	- www.example.com

### 2. Trick your browser to serve local domains

Frankly it has to do with your OS rather than the browser. So to serve some domain from your local server you need to add them to your [Hosts file](https://en.wikipedia.org/wiki/Hosts_(file)), e.g. `/etc/hosts` on OSX:

```
##
# Host Database
#
# localhost is used to configure the loopback interface
# when the system is booting.  Do not change this entry.
##
127.0.0.1           localhost
127.0.0.1           example.com www.example.com
255.255.255.255     broadcasthost
::1                 localhost
fe80::1%lo0         localh
```
**HEADS UP:** You might need to have `root` access to edit this file.

> **Note:** To edit this file on OSX you need to use some editor in terminal using `sudo`
> before the actual editor command or some GUI editor that supports editing of system files.
> I personally use [TextWrangler](http://www.barebones.com/products/textwrangler/),
> but the AppStore build is missing the Command Line tools due to some restrictions.
> You need to install build from their own site.

### 3. Install WordPress

Nothing fancy here. Read the [Codex](http://codex.wordpress.org/Installing_WordPress "Installing WordPress") and than copy this plugin to your `/wp-content/plugins` folder.

### 4. Switch to a new domain

1.  Activate the More Domains plugin
2.  Edit `httpd-vhosts.conf` and change `example.com` to `example.com.dev`
3.  Edit Hosts file again and  change `example.com` to `example.com.dev`
4.  Restart server

The results of edited files shuold be like this:

> /Applications/MAMP/conf/apache/extra/httpd-vhosts.conf

```
ServerName example.com.dev
ServerAlias www.example.com.dev
``` 

> /etc/hosts

```
127.0.0.1           example.com.dev www.example.com.dev
```

### 5. Run http://www.example.com.dev

---

Thanks for your suggestions and love.

[@martin_adamko]('http://twitter.com/martin_adamko')
