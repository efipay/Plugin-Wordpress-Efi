docker-compose up


run with ngrok

1. start ngrok
    ```
    ngrok http 8080
    ```
2. adding the following two lines to wp-config:
    ```
    define('WP_SITEURL', 'http://' . $_SERVER['HTTP_HOST']);
    define('WP_HOME', 'http://' . $_SERVER['HTTP_HOST']);
    ```
3. Installing https://wordpress.org/plugins/relative-url/ in Wordpress
