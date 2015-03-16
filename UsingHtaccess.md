# Using .htacces for resize/crop image #

How to use .htaccess for get cropped/resize image.


# Details #

```
# Begin WP-TimThumb
<IfModule mod_rewrite.c>
    RewriteEngine On
    #RewriteBase /

    #Checks to see if the user is attempting to access a valid file,
    #such as an image or css document, if this isn't true it sends the
    #request to index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?/$1 [L]

    # if is image
    RewriteCond %{REQUEST_URI} (?i)(jpg|jpeg|png|gif)$
    # if have height
    RewriteCond %{QUERY_STRING} h=([1-9])
    # if have width
    RewriteCond %{QUERY_STRING} w=([1-9])
    # is image and have params
    RewriteRule (.*) - [QSA,E=IS_TIMTHUMB:true]
    # if is image rewrite to timtumb else show file directly
    RewriteCond %{ENV:IS_TIMTHUMB} true
    RewriteRule (.*) wp-content/uploads/tt/timthumb.php?src=%{REQUEST_URI}&%{QUERY_STRING}
</IfModule>
# End WP-TimThumb
```

```
<img src="http://path/to/image.png?w=100&h=100">
```