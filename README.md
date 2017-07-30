# 部署说明

## nginx和前端配置

以mac系统为例

1. 新增假域名

  ```
  sudo vim /etc/hosts

  加一行
  127.0.0.1       youkehui-erp-study.io
  ```

2. 配置nginx

  ```
  将根目录的youkehui-erp-study.io复制到/usr/local/etc/nginx/servers

  需要把项目路径修改成自己电脑上的
  set $root_dir /Users/Simon/htdocs/youkehui-erp-study;

  重启nginx

  sudo nginx -s reload
  ```

3. 前端配置

  ```
  cnpm i

  npm run compile:debug
  ```

## 数据库快速部署说明

1. 在mysql中创建数据库 `youkehui-erp-study`
2. 将根目录的youkehui-erp-study.sql导入数据库即可

## 运行

浏览器打开http://youkehui-erp-study.io/

默认登录账号admin  密码ceshi