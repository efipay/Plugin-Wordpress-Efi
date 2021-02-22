=== WP-ngrok ===
Plugin Name: WP-Ngrok
Plugin URI: https://theme.id
Description: Expose your local WordPress to the world
Version: 1.1
Tags: localhost,ngrok,local server,development,debug,callback,api,wp-json,developer
Author: Theme.id
Author URI: https://theme.id
Contributors: themeid,hadie danker
Requires at least: 5.0
Tested up to: 5.5
Stable tag: 1.0.0
Requires PHP: 5.6
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html


Expose your local WordPress to the world. only work in your localhost


== Description ==
Expose a WordPress local web server to the internet ngrok allows you to expose a web server running on your local machine to the internet.
This plugin works by hooking to the start and end of the page creation and capturing it into an output buffer, it then uses the URL from the database for a str_replace, stripping it out before sending back out to the shutdown hook. This means that I can share either the HTTP or HTTPS versions of the ngrok URLs.


=== How To Use ===

==== Step One: Install ngrok====

Download and install ngrok here  [https://ngrok.com/download](https://ngrok.com/download "Download Ngrok")

==== Step Two: Install WP-NGROK====
1. Upload `wp-ngrok.zip` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. run command in your terminal

`~/ngrok http -host-header=localdomain.test 8888`

==== Step Three: Creating the localtunnel====

Send through the host name of the site that we use locally as well as the port number and this will then direct the traffic to my local site. This works whether we had created it, or using something like MAMP Pro to set this up for me.

`~/ngrok http -host-header=sitename.localhost 8888`

Once ngrok is up and running I will be presented with the display that you can see below

`
Session Status                online
Account                       Theme.id (Plan: Pro)
Version                       2.3.35
Region                        United States (us)
Web Interface                 http://127.0.0.1:4040
Forwarding                    http://yourapp.ngrok.io -> http://localhost:8888
Forwarding                    https://yourapp.ngrok.io -> http://localhost:8888
`



= Minimum Requirements =

WordPress 5.0 or greater
PHP version 5.6 or greater
MySQL version 5.0 or greater

= We recommend your host supports: =
PHP version 7.0 or greater
MySQL version 5.6 or greater
WordPress Memory limit of 64 MB or greater (128 MB or higher is preferred)


== Upgrade Notice ==
1.1 Hide notice if not isset HTTP_X_ORIGINAL_HOST
1.0 First Upload


== Installation ==

1. Upload `wp-ngrok` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3.

== Frequently Asked Questions ==

=== Please ask in WordPress Support===
Please to ask about this plugin

== Screenshots ==
1. Demo WP-Ngrok
