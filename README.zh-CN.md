# 微信小程序服务端消息包

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version](https://img.shields.io/packagist/php-v/tourze/wechat-mini-program-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-server-message-bundle) 
[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-mini-program-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-server-message-bundle) 
[![Downloads](https://img.shields.io/packagist/dt/tourze/wechat-mini-program-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-server-message-bundle)  
[![License](https://img.shields.io/packagist/l/tourze/wechat-mini-program-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-server-message-bundle) 
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)](https://github.com/tourze/php-monorepo/actions) 
[![Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

一个用于处理微信小程序服务端消息推送的 Symfony 包，包括消息解密、验证和事件分发功能。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [配置](#配置)
- [快速开始](#快速开始)
- [消息类型](#消息类型)
- [事件系统](#事件系统)
- [高级功能](#高级功能)
- [高级用法](#高级用法)
- [安全](#安全)
- [系统要求](#系统要求)
- [贡献](#贡献)
- [许可证](#许可证)

## 功能特性

- 微信小程序消息推送服务端接口
- 消息签名验证和解密
- 消息自动持久化到数据库
- 事件分发用于自定义消息处理
- 内置消息去重机制
- 支持 JSON 和 XML 消息格式
- 自动用户同步
- 可配置的消息保留策略

## 安装

```bash
composer require tourze/wechat-mini-program-server-message-bundle
```

## 配置

### 1. 注册包

在 `bundles.php` 中添加包：

```php
<?php

return [
    // ... 其他包
    WechatMiniProgramServerMessageBundle\WechatMiniProgramServerMessageBundle::class => ['all' => true],
];
```

## 2. 配置路由

包会自动注册消息处理端点：

```text
/wechat/mini-program/server/{appId}
```

## 3. 环境变量

配置消息保留策略：

```env
# 可选：设置消息保留天数（默认：180）
WECHAT_MINI_PROGRAM_SERVER_MESSAGE_PERSIST_DAY=180
```

## 4. 数据库迁移

创建必要的数据表：

```sql
CREATE TABLE wechat_mini_program_server_message (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT,
    create_time DATETIME,
    msg_id VARCHAR(255),
    to_user_name VARCHAR(255),
    from_user_name VARCHAR(255),
    msg_type VARCHAR(50),
    raw_data JSON,
    INDEX idx_create_time (create_time),
    FOREIGN KEY (account_id) REFERENCES wechat_mini_program_account(id) ON DELETE SET NULL
);
```

## 快速开始

### 1. 设置微信小程序配置

确保你已配置微信小程序账号：
- App ID
- App Secret
- 服务器令牌

### 2. 配置消息推送 URL

在微信小程序控制台中，设置消息推送 URL：

```text
https://your-domain.com/wechat/mini-program/server/{your-app-id}
```

### 3. 处理消息事件

创建事件订阅器来处理传入的消息：

```php
<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;

class WechatMessageSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ServerMessageRequestEvent::class => 'onServerMessage',
        ];
    }

    public function onServerMessage(ServerMessageRequestEvent $event): void
    {
        $message = $event->getMessage();
        $account = $event->getAccount();
        $wechatUser = $event->getWechatUser();

        // 处理不同的消息类型
        switch ($message['MsgType']) {
            case 'text':
                $this->handleTextMessage($message, $account, $wechatUser);
                break;
            case 'image':
                $this->handleImageMessage($message, $account, $wechatUser);
                break;
            // ... 处理其他消息类型
        }
    }

    private function handleTextMessage(array $message, $account, $wechatUser): void
    {
        // 你的文本消息处理逻辑
        $content = $message['Content'];
        // 处理消息...
    }

    private function handleImageMessage(array $message, $account, $wechatUser): void
    {
        // 你的图片消息处理逻辑
        $picUrl = $message['PicUrl'];
        // 处理图片...
    }
}
```

### 4. 访问消息历史

你可以使用仓储访问存储的消息：

```php
<?php

namespace App\Service;

use WechatMiniProgramServerMessageBundle\Repository\ServerMessageRepository;

class MessageService
{
    public function __construct(
        private ServerMessageRepository $messageRepository
    ) {
    }

    public function getRecentMessages(int $limit = 10): array
    {
        return $this->messageRepository->findBy(
            [],
            ['createTime' => 'DESC'],
            $limit
        );
    }
}
```

## 消息类型

该包支持所有微信小程序消息类型：

- **text**: 文本消息
- **image**: 图片消息
- **voice**: 语音消息
- **video**: 视频消息
- **location**: 位置消息
- **link**: 链接消息
- **event**: 事件消息（关注、取消关注等）

## 事件系统

包会为每个传入的消息分发 `ServerMessageRequestEvent`，包含：

- `$message`: 解析后的消息数据
- `$account`: 微信小程序账号
- `$wechatUser`: 微信用户信息

## 高级功能

### 消息去重

包会自动处理消息去重：
- 数据库唯一约束
- 基于缓存的去重（1小时 TTL）

### 用户同步

当收到消息时自动创建和更新用户记录，确保用户数据始终是最新的。

### 消息保留

使用 `WECHAT_MINI_PROGRAM_SERVER_MESSAGE_PERSIST_DAY` 环境变量配置自动消息清理。

## 高级用法

### 自定义消息处理

对于复杂的消息处理场景，你可以扩展默认行为：

```php
<?php

namespace App\Service;

use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdvancedMessageProcessor implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ServerMessageRequestEvent::class => ['processMessage', 100], // 高优先级
        ];
    }

    public function processMessage(ServerMessageRequestEvent $event): void
    {
        $message = $event->getMessage();
        
        // 实现高级处理逻辑
        if ($this->requiresSpecialHandling($message)) {
            $this->performAdvancedProcessing($message);
            // 可选择停止传播以防止其他监听器处理
            $event->stopPropagation();
        }
    }

    private function requiresSpecialHandling(array $message): bool
    {
        // 你的自定义逻辑来确定消息是否需要特殊处理
        return isset($message['SpecialFlag']);
    }

    private function performAdvancedProcessing(array $message): void
    {
        // 你的高级处理实现
    }
}
```

### 自定义验证

你可以为传入的消息添加自定义验证：

```php
<?php

namespace App\Validator;

use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MessageValidator implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ServerMessageRequestEvent::class => ['validateMessage', 200], // 很高优先级
        ];
    }

    public function validateMessage(ServerMessageRequestEvent $event): void
    {
        $message = $event->getMessage();
        
        if (!$this->isValidMessage($message)) {
            throw new BadRequestHttpException('消息格式无效');
        }
    }

    private function isValidMessage(array $message): bool
    {
        // 你的自定义验证逻辑
        return isset($message['FromUserName']) && isset($message['ToUserName']);
    }
}
```

## 安全

### 消息验证

包会自动验证消息签名以确保真实性：

- 所有传入的消息都会根据微信的签名算法进行验证
- 无效的签名会被拒绝并返回适当的错误响应
- 消息解密使用你配置的 App Secret 进行

### 数据保护

- 所有消息数据在存储前都会进行验证
- 敏感信息在传输过程中得到适当加密
- 用户数据按照隐私最佳实践进行处理

### 限流

考虑为你的消息端点实现限流：

```php
<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class RateLimitSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        if (str_contains($request->getPathInfo(), '/wechat/mini-program/server/')) {
            if ($this->isRateLimited($request)) {
                throw new TooManyRequestsHttpException();
            }
        }
    }

    private function isRateLimited($request): bool
    {
        // 实现你的限流逻辑
        return false;
    }
}
```

## 系统要求

- PHP 8.1+
- Symfony 7.3+
- Doctrine ORM 3.0+
- Doctrine DBAL 4.0+
- 微信小程序账号

## 贡献

请查看 [CONTRIBUTING.md](CONTRIBUTING.md) 了解详情。

## 许可证

MIT 许可证。请查看 [License File](LICENSE) 了解更多信息。