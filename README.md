# [botble iyzico](https://github.com/damalis/botble-iyzico)

Iyzico payment gateway for [Botble Technologies products](https://botble.com/)

<p align="left"> <a href="https://www.iyzico.com/" target="_blank" rel="noreferrer"> <img src="https://avatars.githubusercontent.com/u/3815564?s=200&v=4" alt="iyzico"/> </a>&nbsp;&nbsp;&nbsp;
<a href="https://botble.com/" target="_blank" rel="noreferrer"> <img src="https://avatars.githubusercontent.com/u/13820353?s=200&v=4" alt="botble technologies"/> </a></p>

#### With this project you can quickly run the following:

- [iyzico](https://github.com/iyzico/iyzipay-php)
- [Botble](https://github.com/botble)

## Installation

```
composer require damalis/botble-iyzico
```

- Run these commands below to complete the setup

```
composer dump-autoload
```

- iyzico logo file will be copied to the specified location

```
php artisan cms:plugin:assets:publish iyzico
```

```
php artisan optimize:clear
```

## Usage

change 'same_site' => 'lax' to 'same_site' => null in ```./config/session.php``` file.

Go to Admin -> Plugins -> Installed plugins and actived Iyzico

then
 
Admin -> Payments -> Payment methods -> Iyzico

[Test account for payment](https://sandbox-merchant.iyzipay.com/auth/login)

[Test Card Details](https://docs.iyzico.com/en/add-ons/test-cards)

tried with [Shofy. Version 1.0.3](https://docs.botble.com/shofy/) product