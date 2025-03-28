# Livewire Telegram Auth

A Laravel 12 and Livewire package that provides an easy way to authenticate users via Telegram.

## Requirements

-   PHP 8.2+
-   Laravel 12.x
-   Livewire 3.0+
-   TgWebValid 4.2+

## Installation

You can install the package via composer:

```bash
composer require scriptoshi/livewire-telegram-auth
```

## Configuration

### 1. Add Telegram Bot Token to your .env file

```
TELEGRAM_BOT_TOKEN=123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZ
```

Note: Use the full token including the bot ID (numbers before the colon) and the token part (after the colon). The component will automatically extract the bot ID for authentication.

### 2. Add Telegram Bot configuration to your config/services.php

```php
'telegram' => [
    'telegramBotToken' => env('TELEGRAM_BOT_TOKEN'),
    'profile_photo_disk' => 'public', // Storage disk for profile photos
],
```

### 3. Database Schema

This package adds three fields to your users table:

-   `telegramId` - The Telegram user identifier
-   `telegram_avatar_url` - URL to the user's Telegram avatar image
-   `telegram_photo_path` - Local path to the user's profile photo

The good news is that you don't need to modify your User model's `$fillable` array. The package sets these fields directly on the model instance.

### 4. Publish and run migrations

```bash
php artisan vendor:publish --tag=telegram-auth-migrations
php artisan migrate
```

Migrations will make the password and Email Field `nullable` and these field will be nulled for users who authenticate via telegram. so beware if you typehint or expect these to be a string.

### 5. Configure Tailwind CSS

To ensure that Tailwind CSS properly processes the component styles, add this package to your content sources in your CSS file (typically `resources/css/app.css`):

```css
/* Add this line with your other @source directives */
@source '../../vendor/scriptoshi/livewire-telegram-auth/resources/views/**/*.blade.php';
```

For example, your CSS file might look similar to this:

```css
@import "tailwindcss";

@source "../views";
@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../vendor/scriptoshi/livewire-telegram-auth/resources/views/**/*.blade.php';

/* Rest of your CSS file */
```

### 6. (Optional) Publish views

```bash
php artisan vendor:publish --tag=telegram-auth-views
```

## Usage

Simply include the Livewire component in your login page:

```blade
<livewire:telegram-login />
```

## Customization

### User Registration Logic

If you need to customize the user registration process, you can extend the `TelegramLogin` component and override the `register` method:

```php
<?php

namespace App\Livewire;

use Scriptoshi\LivewireTelegramAuth\Components\TelegramLogin as BaseTelegramLogin;

class CustomTelegramLogin extends BaseTelegramLogin
{
    protected function register($udata)
    {
        // Your custom registration logic here
        // ...

        // Call parent method or implement your own logic
        return parent::register($udata);
    }
}
```

Then register your custom component in a service provider:

```php
Livewire::component('custom-telegram-login', CustomTelegramLogin::class);
```

And use it in your blade file:

```blade
<livewire:custom-telegram-login />
```

## Credits

-   [Scriptoshi](https://github.com/scriptoshi)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
