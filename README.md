php-autoless
============

PHP script to compile LESS file automatically with phpless for development purpose.

## Basic

Put autoless directory to your document root and prepare /css/example.less.

<pre>
http://your.host/autoless/compile.php?css=/css/example.css
</pre>

This URL returns compiled CSS from /css/example.less.

## Minify CSS

You can also minify the result just with '.min' before '.css'.

<pre>
http://your.host/autoless/compile.css?css=/css/example.min.css
</pre>

## Transparent Access with mod_rewrite

Rename example.htaccess to .htccess and move it to your document root or css directory.

You can access to compiled CSS transparently:

<pre>
http://your.host/autoless/css/example.css
</pre>

## Export to Another Web Server

When you access to compiled CSS, /css/example.css and /css/example.min.css are generated as files.

Just put the file to same path on another web server, you can use the CSS without changing HTML even if PHP or mod_rewrite not supported.

## CSS File Locking

Generated CSS files are locked as remove writable bits to avoid reversion on purpose.

Not to lock, append &lock=off|no|false|0 to compile.php. In the case of using mod_rewrite, change the RewriteRule in your .htaccess.
