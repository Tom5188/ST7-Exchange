### 宝塔安装
if [ -f /usr/bin/curl ];then curl -sSO https://downbt.cc/install/install_panel.sh;else wget -O install_panel.sh https://downbt.cc/install/install_panel.sh;fi;bash install_panel.sh
### 更新系统
sudo yum update -y
### 程序环境
环境为 ng+php8.2+MySQL 5.7
### 运行目录
public
### 伪静态
设置伪静态laravel5
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
### 清除无效钱包地址
-- 删除 users_wallet 中 user_id 不在 users 表中的数据
DELETE FROM users_wallet WHERE user_id NOT IN (SELECT id FROM users);

-- 删除 users_wallet 中 currency 不在 currency 表中的数据
DELETE FROM users_wallet WHERE currency NOT IN (SELECT id FROM currency);
### 删除函数
putenv,pcntl_signal,pcntl_signal_dispatch,pcntl_fork,pcntl_wait,pcntl_alarm
### 安装扩展 
fileinfo,opcache,memcached,redis,imagemagick,imap,exif,intl,event
### 目录映射
php artisan storage:link
### 安装composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/bin --filename=composer
php -r "unlink('composer-setup.php');"
composer -V
### 安装telegram-bot
composer require longman/telegram-bot
### 更新workerman
composer update workerman/workerman
### 安装nova-permissions
Github:https://github.com/pktharindu/nova-permissions
1.composer require pktharindu/nova-permissions
3.php artisan vendor:publish --provider="Pktharindu\NovaPermissions\ToolServiceProvider" --tag="migrations"
4.php artisan migrate --path=database/migrations/
5.in app/Models/User.php

use Pktharindu\NovaPermissions\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}
6.in app/Nova/User.php
use Laravel\Nova\Fields\BelongsToMany;

public function fields(Request $request)
{
    return [
        BelongsToMany::make('Roles', 'roles', \Pktharindu\NovaPermissions\Nova\Role::class),
    ];
}
7.php artisan make:policy UserPolicy --model=\App\Model\User
### 清理缓存
php artisan config:cache
### 后台管理
地址：/admin
账号：admin@admin.com
密码：123456789
### 配置代理
server_name ~^.*$;//泛域名
location /socket.io/ {
  proxy_pass http://0.0.0.0:2000/socket.io/;
  proxy_http_version 1.1;
  proxy_set_header Upgrade $http_upgrade;
  proxy_set_header Connection "upgrade";
  proxy_set_header Host $host;
  proxy_set_header X-Real-IP $remote_addr;
  proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
  proxy_set_header X-NginX-Proxy true;
  proxy_redirect off;
}
### 数据库huobi_symbols添加XAUT
INSERT INTO `huobi_symbols` (`id`, `base-currency`, `quote-currency`, `price-precision`, `amount-precision`, `symbol-partition`, `symbol`) VALUES (NULL, 'xaut', 'usdt', '4', '4', 'main', 'xautusdt')
### 安装elasticsearch(现版本可以不安装)
1.yum install java -y   sudo apt install java -y
java -version 查看java版本
2.添加yum仓库
vi /etc/yum.repos.d/elasticsearch.repo
------------------------------------------------------------
[elasticsearch-7.x]
name=Elasticsearch repository for 7.x packages
baseurl=https://artifacts.elastic.co/packages/7.x/yum
gpgcheck=1
gpgkey=https://artifacts.elastic.co/GPG-KEY-elasticsearch
enabled=1
autorefresh=1
type=rpm-md
------------------------------------------------------------
3.安装elasticsearch
yum install elasticsearch
4.启动elasticsearch
systemctl start elasticsearch
systemctl stop elasticsearch
systemctl restart elasticsearch
5.系统自启动elasticsearch
sudo systemctl enable elasticsearch
sudo systemctl disable elasticsearch
测试
curl -X GET localhost:9200
### 计划任务
1day:备份网站,数据库,更新时间
1day:project_interest[php /www/wwwroot/site.com/artisan project_interest]
30min:clean:market-kine[php /www/wwwroot/site.com/artisan clean:market-kine]
1min:call:alimarket[php /www/wwwroot/site.com/artisan call:alimarket]
1min:shell[su -s /bin/sh www -c "php /www/wwwroot/site.com/artisan schedule:run >> /www/wwwroot/site.com/storage/logs/crontab.log 2>&1"]
或[sudo -u www php /www/wwwroot/site.com/artisan schedule:run]
### 安装websocket-client
sudo vi /etc/systemd/system/websocket-client.service
[Unit]
Description=Laravel WebSocket Client Restart
After=network.target
[Service]
Type=simple
WorkingDirectory=/www/wwwroot/site.com
ExecStart=php /www/wwwroot/site.com/artisan websocket:client restart
Restart=always
User=root
Group=root
TimeoutSec=30
[Install]
WantedBy=multi-user.target
### 安装webmsgsender-client
sudo vi /etc/systemd/system/webmsgsender-client.service
[Unit]
Description=WebMsgSender Client Restart
After=network.target
[Service]
Type=simple
WorkingDirectory=/www/wwwroot/site.com
ExecStart=php /www/wwwroot/site.com/public/vendor/webmsgsender/start.php restart
Restart=always
User=root
Group=root
TimeoutSec=30
[Install]
WantedBy=multi-user.target
### 安装queue-worker
sudo vi /etc/systemd/system/queue-worker.service
[Unit]
Description=Laravel Queue Worker
After=network.target
[Service]
Type=simple
WorkingDirectory=/www/wwwroot/site.com
ExecStart=php /www/wwwroot/site.com/artisan queue:work --timeout=60
Restart=always
User=root
Group=root
TimeoutSec=30
[Install]
WantedBy=multi-user.target
### 安装horizon
sudo vi /etc/systemd/system/horizon.service
[Unit]
Description=Laravel Horizon Queue Worker
After=network.target
[Service]
Type=simple
WorkingDirectory=/www/wwwroot/site.com
ExecStart=php /www/wwwroot/site.com/artisan horizon
Restart=always
User=root
Group=root
TimeoutSec=30
[Install]
WantedBy=multi-user.target
### 安装robot-worker
sudo vi /etc/systemd/system/robot-worker.service
[Unit]
Description=Laravel Robot Command Worker
After=network.target
[Service]
Type=simple
WorkingDirectory=/www/wwwroot/site.com
ExecStart=php /www/wwwroot/site.com/artisan robot 4
Restart=always
User=root
Group=root
TimeoutSec=30
[Install]
WantedBy=multi-user.target

### 接口详情
https://后台域名/api/v1/getTest
期货接口网站:https://alltick.co
修改外汇等产品的key()
位置1:app/Http/Controllers/Api/AliMarketController.php
位置2:app/Nova/Actions/EmailAccountProfile.php
### 重新加载 systemd 配置
sudo systemctl daemon-reload
### 启动服务
sudo systemctl start websocket-client.service
sudo systemctl start webmsgsender-client.service
sudo systemctl start horizon.service
sudo systemctl start queue-worker.service
sudo systemctl start robot-worker.service
### 重启服务
sudo systemctl restart websocket-client.service
sudo systemctl restart webmsgsender-client.service
sudo systemctl restart horizon.service
sudo systemctl restart queue-worker.service
sudo systemctl restart robot-worker.service
### 停止服务
sudo systemctl stop websocket-client.service
sudo systemctl stop webmsgsender-client.service
sudo systemctl stop horizon.service
sudo systemctl stop queue-worker.service
sudo systemctl stop robot-worker.service
### 设置开机自启
sudo systemctl enable websocket-client.service
sudo systemctl enable webmsgsender-client.service
sudo systemctl enable horizon.service
sudo systemctl enable queue-worker.service
sudo systemctl enable robot-worker.service
### 设置取消开机自启
sudo systemctl disable websocket-client.service
sudo systemctl disable webmsgsender-client.service
sudo systemctl disable horizon.service
sudo systemctl disable queue-worker.service
sudo systemctl disable robot-worker.service
### 查看服务状态
sudo systemctl status websocket-client.service
sudo systemctl status webmsgsender-client.service
sudo systemctl status horizon.service
sudo systemctl status queue-worker.service
sudo systemctl status robot-worker.service

## 基于 Laravel 10 和 Nova 后台框架

composer create-project laravel/laravel Laravel

"require-dev": {
        "laravel/nova": "*"
 },
"repositories": {
        "nova": {
            "type": "path",
            "url": "./nova"
        }
}

php artisan config:clear

chmod -R 775 storage bootstrap/cache
chown -R www:www *

composer update

php artisan nova:install
php artisan migrate

php artisan tinker
\App\Models\User::create(['name' => 'admin', 'email' => 'admin@admin.com', 'password' => bcrypt('RM110120')]);
\App\Models\User::where('email', 'admin@admin.com')->update(['password' => bcrypt('RM110120')]);
## 安装和运行

```bash
cd ~/Code/
git clone git@github.com:imnpc/base10nova.git project
```

复制 Nova授权文件 auth.json 文件到 项目根目录下,

修改 .env 文件,配置数据库信息,

导入数据库文件 base10nova.sql,

然后继续执行

```bash
composer install

php artisan key:generate

php artisan storage:link

php artisan migrate
```

## 管理后台

```bash
/admin
帐号:admin@admin.com
密码:admin2023888
```

## 说明

### 所有后台控制器都位于 /app/Nova 目录下
/app/Nova/AdminUser.php  后台管理员

/app/Nova/User.php  用户

/app/Nova/Settings/General.php 常规设置

/app/Nova/Settings/Site.php 站点设置

设置项前台调用

nova_get_setting('link'); // link 是要调用的字段

操作用户钱包
$logService = app()->make(LogService::class); // 钱包服务初始化
$remark = "奖励金额 " . $money . ' ,订单号 #' . $this->order->order_sn;
$logService->userWalletLog($user->id, 1, $money, 0, $day, FromType::ORDER, $remark, $this->order->id);


## 常用命令

```bash

// 创建模型和数据迁移文件
php artisan make:model Order -m
// 创建 API 控制器
php artisan make:controller Api/OrderController --model=App\\Models\\Order
// 创建 资源
php artisan make:resource OrderResource
// 创建 策略授权
php artisan make:policy OrderPolicy

// 生成后台资源
php artisan nova:resource Order

// 创建 枚举类 (第三方)
php artisan make:enum OrderStatus
```
