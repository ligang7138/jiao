# Laravel API - 后端服务

基于 Laravel 11 + PHP 8.4 的前后端分离后端 API 服务。

## 技术栈

- PHP 8.4
- Laravel 11
- MySQL 8.0
- Redis
- Nginx

## 快速开始

### 开发环境启动

```bash
# 启动开发环境
docker compose up -d

# 查看服务状态
docker compose ps

# 进入 PHP 容器
docker compose exec php bash

# 安装依赖
docker compose exec php composer install

# 运行迁移
docker compose exec php php artisan migrate

# 生成 JWT 密钥
docker compose exec php php artisan jwt:secret
```

### 访问地址

- API: http://localhost:8001
- MySQL: localhost:33061
- Redis: localhost:63791

## 目录结构

```
laravel-api/
├── app/                    # 应用核心代码
│   ├── Http/              # HTTP 层
│   │   ├── Controllers/   # 控制器
│   │   ├── Middleware/    # 中间件
│   │   ├── Requests/      # 表单请求验证
│   │   └── Resources/     # API 资源
│   ├── Services/          # 服务层
│   ├── Actions/           # 业务动作层
│   ├── Models/            # Eloquent 模型
│   ├── Policies/          # 权限策略
│   └── Constants/         # 常量定义
├── config/                # 配置文件
├── database/              # 数据库
│   ├── migrations/        # 迁移文件
│   └── seeders/           # 数据填充
├── routes/                # 路由定义
├── storage/               # 存储目录
├── tests/                 # 测试
└── docker/                # Docker 配置
```

## API 文档

启动服务后访问: http://localhost:8001/api/documentation
