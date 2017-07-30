server {
    set $root_dir /Users/Simon/htdocs/youkehui-erp-study;
    set $webpack_dev_server http://127.0.0.1:3031;
  
    listen 80;

    server_name youkehui-erp-study.io;

    root $root_dir/web;

    access_log /usr/local/var/log/nginx/youkehui-erp-study.io.access.log;
    error_log /usr/local/var/log/nginx/youkehui-erp-study.io.error.log;
    
    location / {
        try_files $uri /app_dev.php$is_args$args;
    }
    
    location ~ ^/(app|app_dev)\.php(/|$) {
        fastcgi_pass 127.0.0.1:9000;
        # fastcgi_pass unix:/var/run/php5.6-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
        fastcgi_param  HTTPS              off;
        fastcgi_param HTTP_X-Sendfile-Type X-Accel-Redirect;
        fastcgi_param HTTP_X-Accel-Mapping /udisk=$root_dir/app/data/udisk;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 8 128k;
    }
    
    location @webpack_dev {
        proxy_pass $webpack_dev_server;
    }
    
    location ~ ^/static {
        try_files $uri @webpack_dev;
    }

    location ~ ^/udisk {
        internal;
        root $root_dir/app/data/;
    }

    location ~* \.(jpg|jpeg|gif|png|ico|swf)$ {
        expires 3y;
        access_log off;
        gzip off;
    }

    location ~* \.(css|js)$ {
        access_log off;
        expires 3y;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        # fastcgi_pass unix:/var/run/php5.6-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
    }
}
