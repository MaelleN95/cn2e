FROM httpd:2.4-alpine

RUN sed -i \
    -e 's|#LoadModule proxy_module modules/mod_proxy.so|LoadModule proxy_module modules/mod_proxy.so|' \
    -e 's|#LoadModule proxy_fcgi_module modules/mod_proxy_fcgi.so|LoadModule proxy_fcgi_module modules/mod_proxy_fcgi.so|' \
    -e 's|#LoadModule rewrite_module modules/mod_rewrite.so|LoadModule rewrite_module modules/mod_rewrite.so|' \
    -e 's|#LoadModule headers_module modules/mod_headers.so|LoadModule headers_module modules/mod_headers.so|' \
    /usr/local/apache2/conf/httpd.conf

RUN printf '\nServerName localhost\nDocumentRoot "/var/www/html/public"\n<Directory "/var/www/html/public">\n    AllowOverride All\n    Require all granted\n</Directory>\nDirectoryIndex index.php\n' >> /usr/local/apache2/conf/httpd.conf

RUN printf '\n<FilesMatch "\\.php$">\n    SetHandler "proxy:fcgi://cn2e-php:9000"\n</FilesMatch>\n' >> /usr/local/apache2/conf/httpd.conf

EXPOSE 80

CMD ["httpd", "-D", "FOREGROUND"]