# Lumen Framework 9.x

## 项目结构

### 基础目录

| 目录           | 说明       | 备注                                 |
|--------------|----------|------------------------------------|
| app          | 应用目录     |                                    |
| bootstrap    | 应用启动相关文件 |                                    |
| config       | 应用配置文件   |                                    |
| lang         | 多国语言     |                                    |
| resources    | 资源目录     | Blade 视图、XML 响应模板                  |
| routes       | 路由文件     |                                    |
| storage/app  | 应用存储目录   | 存储用于生成 JWT 的公私钥、微信证书等（**不加入版本控制**） |
| storage/logs | 日志目录     | 错误、调试等日志位于日志根目录，其它日志按文件夹区分         |
| tests        | 单元测试目录   |                                    |

### 应用目录

应用目录为 `app`，表格内目录为相对路径

| 目录              | 说明                     | 备注                                                                        |
|-----------------|------------------------|---------------------------------------------------------------------------|
| Common          | 公共类，多为工具向              | 工具相关类如需实例化，通过内置方法 `app(ClassName::class)` 实现或自行实现静态方法 `getInstance` 获取实例  |
| Constants       | 常量                     | 需加入到 Composer Autoload 中的 `files`                                         |
| Events          | 事件类                    ||
| Factories       | 工厂模式相关类                | 按需                                                                        |
| Helpers         | 助手函数                   | 需加入到 Composer Autoload 中的 `files`                                         |
| Http/Controller | 控制器                    | 智慧律所前台接口按版本划分，其它模块独立文件夹。控制器调用服务类通过 **依赖注入** 实现实例化                         |
| Http/Middleware | 中间件，子目录 `Route` 为路由中间件 |                                                                           |
| Jobs            | 队列类                    ||
| Listeners       | 事件监听器                  ||
| Mail            | 邮件类                    | 按需                                                                        |
| Model/Entities  | 数据实体目录，对应数据表           |                                                                           |
| Model/Logic     | 数据逻辑目录，实现数据 ORM 相关操作   |                                                                           |
| Services        | 业务逻辑服务                 | 按版本划分，调用数据逻辑类通过 **依赖注入** 实现实例化。服务之间调用通过内置方法 `app(ClassName::class)` 实现实例化 |

## 密钥生成

默认使用 ECC，将生成的密钥放置于 `storage/app` 目录下

```shell
openssl ecparam -genkey -name prime256v1 -out ecc_pri_key.pem
openssl ec -in ecc_pri_key.pem -pubout -out ecc_pub_key.pem
```

## 请求调试

### 请求头

按照规范，所有请求头 **Name** 应当在请求及响应过程中使用小写

| 请求头           | 值          |
|---------------|------------|
| authorization | Bearer JWT |
| content-type  | -          |

### 响应头

| 响应头            | 值   | 说明               |
|----------------|-----|------------------|
| authentication | JWT | Token 通过该头响应给请求者 |

## 助手方法

### 响应

| 方法名       | 参数               | 说明     | 备注                                                              |
|-----------|------------------|--------|-----------------------------------------------------------------|
| `success` | 成功提示及 Json 内容    | 成功响应   | 如需返回列表时，应对其加上 Key：`return success('OK', 'list_name' => [...]);` |
| `failed`  | 失败提示及错误码         | 失败响应   |                                                                 |
| `xml`     | XML 内容           | 响应 XML |                                                                 |
| `image`   | 图片内容字符串及格式（格式可选） | 响应图片   | 格式为 png、jpg、jpeg、gif 等                                          |

### 其它

| 方法名           | 说明               | 备注                                               |
|---------------|------------------|--------------------------------------------------|
| `getSetCache` | 简易缓存获取及设置        | 获取缓存内容，第二个参数可传入闭包                                |
| `ex_mt_rand`  | 高级生成随机数          | 返回字符串，可出现类似 `000123` 的数值，提高了随机范围                 |
| `ex_str_rand` | 生成随机字符串          |                                                  |
| `formatPrice` | 格式化价格，无需除以 `100` | 返回字符串，示例：`218.65`                                |
| `getResLink`  | 获取静态资源链接         | 数据库中存储的资源路径为 `/` 开头或完整 `http/https` 路径可通过该方法自动处理 |

## 图片优化组件

二进制程序安装命令

| Ubuntu                           | CentOS                           | macOS                    |
|----------------------------------|----------------------------------|--------------------------|
| `sudo apt-get install jpegoptim` | `sudo dnf install epel-release`  | `brew install jpegoptim` |
| `sudo apt-get install optipng`   | `sudo dnf install jpegoptim`     | `brew install optipng`   |
| `sudo apt-get install pngquant`  | `sudo dnf install optipng`       | `brew install pngquant`  |
| `sudo apt-get install gifsicle`  | `sudo dnf install pngquant`      | `brew install gifsicle`  |
| `sudo apt-get install webp`      | `sudo dnf install gifsicle`      | `brew install webp`      |
| `sudo npm install -g svgo`       | `sudo dnf install libwebp-tools` | `npm install -g svgo`    |
|                                  | `sudo npm install -g svgo`       |                          |

## Metrics 静态扫描

代码静态扫描用于代码质量优化，PHP 8+ 需关闭 `Xdebug` 扩展

```shell
# 安装
composer global require phpmetrics/phpmetrics

# 生成静态报告
phpmetrics --report-html=report app
```

## 队列任务

执行：

```shell
php artisan queue:work
```

队列名需根据项目名称加上前缀

| 队列名称                     | 所属类别 | 说明             |
|--------------------------|------|----------------|
| prefix_order_auto_cancel | 订单   | 订单超时 30 分钟自动取消 |

## 异常处理

### 请求异常

404 不存在、405 方法不可用已加入异常处理 `App\Exceptions\Handler`，无需额外操作。表单验证中的异常也已自动处理，参考 `LoginController` 将异常提示加入到注释中即可

## 数据层

### 数据实体

所有数据实体位于 `app/Models/Entities` 并继承该目录下的 `Model`，用户模型需注意按示例实现 `AuthenticatableContract`、`AuthorizableContract`，并且引入 3
个 `trait`

### 数据逻辑

所有对数据库操作的业务逻辑位于 `app/Models/Logic`，根据实际调用情况可选继承 `Logic`，仅逻辑层可调用实体层

## 服务层

所有服务位于 `app/Services`，所有的数据逻辑仅服务层可调用，服务之间互相调用使用 `app` 方法实现实例化

## 代码提示优化

项目根目录执行 `php artisan ide-helper:g && php artisan ide-helper:meta` 即可
