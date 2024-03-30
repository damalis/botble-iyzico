# [botble iyzico](https://github.com/damalis/botble-iyzico)

Iyzico payment gateway for [Botble Technologies products](https://botble.com/)

<p align="left"> <a href="https://www.iyzico.com/" target="_blank" rel="noreferrer"> <img src="https://avatars.githubusercontent.com/u/3815564?s=200&v=4" alt="iyzico" height="40" width="40"/> </a>&nbsp;&nbsp;&nbsp; <a href="https://botble.com/" target="_blank" rel="noreferrer"> <img src="https://avatars.githubusercontent.com/u/13820353?s=200&v=4" alt="botble technologies" width="40" height="40" width="40"/> </a>


## Installation

```
composer require iyzico/iyzipay-php
```

then

- download repository zip file  [botble-iyzico](https://github.com/damalis/botble-iyzico/archive/refs/heads/main.zip)
- unzip and rename "botble-iyzico-main" folder to "iyzico"
- copy iyzico folder to ./platform/plugins directory
- finally, copy iyzico folder in iyzico directory to ./public/vendor/core/plugins director.

## Usage

change 'same_site' => 'lax' to 'same_site' => null in ```./config/session.php``` file.

Go to Admin -> Plugins -> Installed plugins and actived Iyzico

then
 
Admin -> Payments -> Payment methods -> Iyzico

[Test account for payment](https://sandbox-merchant.iyzipay.com/auth/login)

tried with [Shofy. Version 1.0.3](https://docs.botble.com/shofy/) product
