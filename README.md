# WeChat Mini Program Server Message Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version](https://img.shields.io/packagist/php-v/tourze/wechat-mini-program-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-server-message-bundle) 
[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-mini-program-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-server-message-bundle) 
[![Downloads](https://img.shields.io/packagist/dt/tourze/wechat-mini-program-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-server-message-bundle)  
[![License](https://img.shields.io/packagist/l/tourze/wechat-mini-program-server-message-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-server-message-bundle) 
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)](https://github.com/tourze/php-monorepo/actions) 
[![Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

A Symfony bundle for handling WeChat Mini Program server-side message push 
notifications, including message decryption, validation, and event dispatching.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Message Types](#message-types)
- [Event System](#event-system)
- [Advanced Features](#advanced-features)
- [Advanced Usage](#advanced-usage)
- [Security](#security)
- [Requirements](#requirements)
- [Contributing](#contributing)
- [License](#license)

## Features

- WeChat Mini Program message push server endpoint
- Message signature verification and decryption
- Automatic message persistence to database
- Event dispatching for custom message handling
- Built-in deduplication mechanism
- Support for both JSON and XML message formats
- Automatic user synchronization
- Configurable message retention policies

## Installation

```bash
composer require tourze/wechat-mini-program-server-message-bundle
```

## Configuration

### 1. Register the Bundle

Add the bundle to your `bundles.php`:

```php
<?php

return [
    // ... other bundles
    WechatMiniProgramServerMessageBundle\WechatMiniProgramServerMessageBundle::class => ['all' => true],
];
```

## 2. Configure Routing

The bundle automatically registers the message handling endpoint at:

```text
/wechat/mini-program/server/{appId}
```

## 3. Environment Variables

Configure message retention policy:

```env
# Optional: Set message retention days (default: 180)
WECHAT_MINI_PROGRAM_SERVER_MESSAGE_PERSIST_DAY=180
```

## 4. Database Migration

Create the required database table:

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

## Quick Start

### 1. Set Up WeChat Mini Program Configuration

Ensure you have a WeChat Mini Program account configured with:
- App ID
- App Secret
- Server Token

### 2. Configure Message Push URL

In WeChat Mini Program console, set the message push URL to:

```text
https://your-domain.com/wechat/mini-program/server/{your-app-id}
```

### 3. Handle Message Events

Create an event subscriber to handle incoming messages:

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

        // Handle different message types
        switch ($message['MsgType']) {
            case 'text':
                $this->handleTextMessage($message, $account, $wechatUser);
                break;
            case 'image':
                $this->handleImageMessage($message, $account, $wechatUser);
                break;
            // ... handle other message types
        }
    }

    private function handleTextMessage(array $message, $account, $wechatUser): void
    {
        // Your text message handling logic
        $content = $message['Content'];
        // Process the message...
    }

    private function handleImageMessage(array $message, $account, $wechatUser): void
    {
        // Your image message handling logic
        $picUrl = $message['PicUrl'];
        // Process the image...
    }
}
```

### 4. Access Message History

You can access stored messages using the repository:

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

## Message Types

The bundle supports all WeChat Mini Program message types:

- **text**: Text messages
- **image**: Image messages
- **voice**: Voice messages
- **video**: Video messages
- **location**: Location messages
- **link**: Link messages
- **event**: Event messages (subscribe, unsubscribe, etc.)

## Event System

The bundle dispatches `ServerMessageRequestEvent` for each incoming message, containing:

- `$message`: The parsed message data
- `$account`: The WeChat Mini Program account
- `$wechatUser`: The WeChat user information

## Advanced Features

### Message Deduplication

The bundle automatically handles message deduplication using:
- Database unique constraints
- Cache-based deduplication (1-hour TTL)

### User Synchronization

Automatically creates and updates user records when messages are received, 
ensuring user data is always current.

### Message Retention

Configure automatic message cleanup using the 
`WECHAT_MINI_PROGRAM_SERVER_MESSAGE_PERSIST_DAY` environment variable.

## Advanced Usage

### Custom Message Processing

For complex message processing scenarios, you can extend the default behavior:

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
            ServerMessageRequestEvent::class => ['processMessage', 100], // High priority
        ];
    }

    public function processMessage(ServerMessageRequestEvent $event): void
    {
        $message = $event->getMessage();
        
        // Implement advanced processing logic
        if ($this->requiresSpecialHandling($message)) {
            $this->performAdvancedProcessing($message);
            // Optionally stop propagation to prevent other listeners
            $event->stopPropagation();
        }
    }

    private function requiresSpecialHandling(array $message): bool
    {
        // Your custom logic to determine if message needs special handling
        return isset($message['SpecialFlag']);
    }

    private function performAdvancedProcessing(array $message): void
    {
        // Your advanced processing implementation
    }
}
```

### Custom Validation

You can add custom validation for incoming messages:

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
            ServerMessageRequestEvent::class => ['validateMessage', 200], // Very high priority
        ];
    }

    public function validateMessage(ServerMessageRequestEvent $event): void
    {
        $message = $event->getMessage();
        
        if (!$this->isValidMessage($message)) {
            throw new BadRequestHttpException('Invalid message format');
        }
    }

    private function isValidMessage(array $message): bool
    {
        // Your custom validation logic
        return isset($message['FromUserName']) && isset($message['ToUserName']);
    }
}
```

## Security

### Message Verification

The bundle automatically verifies message signatures to ensure authenticity:

- All incoming messages are validated against WeChat's signature algorithm
- Invalid signatures are rejected with appropriate error responses
- Message decryption is performed using your configured App Secret

### Data Protection

- All message data is validated before storage
- Sensitive information is properly encrypted during transmission
- User data is handled according to privacy best practices

### Rate Limiting

Consider implementing rate limiting for your message endpoints:

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
        // Implement your rate limiting logic
        return false;
    }
}
```

## Requirements

- PHP 8.1+
- Symfony 7.3+
- Doctrine ORM 3.0+
- Doctrine DBAL 4.0+
- WeChat Mini Program Account

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
