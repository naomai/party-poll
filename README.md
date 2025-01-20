# Party Poll
<img alt='Pizza sliced into parts reminescent of pie chart' src='docs/pizza-svgrepo-com.svg' style="float: right; width: 150px;"/>

## About
Quickly ask your friends about anything 🍕🪩🎵🎄 

**This project is in early development**, most of the features are yet to be 
fully functional.

## Features
- 🔗 Invitation-based - hop in with link or QR Code. 
- 👑 Prepare your master questions and release them when they're ready. 
- ✨ Came up with new ideas? Add some more questions later, everybody will get notifications to check out the poll.
- 👽 No need for registering accounts, just type your name - or don't. Lorem-ipsum names are underrated ✌️
- 🏀 It's real-time!

![Website showing two questions with possible options](docs/overview_alpha1.png)

## Installation

### Standalone

`PartyPoll` requires following:
* PHP 8.2 or later
* Web server with FastCGI support (Apache/Nginx)
* [Composer](https://getcomposer.org/)
* [NodeJS](https://nodejs.org/en)
* [MySQL](https://www.mysql.com/) or other database [supported by Laravel](https://laravel.com/docs/11.x/database#introduction)

Prepare an empty database with dedicated user.

Clone this repository to your server, and point the document root of web server to `/public` directory. Run the following:

```sh
composer install
npm install
npm run build
```
Now, we need to let the app know its URL, and the database connection details. Edit following fields in `.env` file:
```ini
APP_URL=http://your-app-address.com/ # URL that points to document root

[...]

DB_CONNECTION=mysql # change if using other DB engine
DB_HOST=###
DB_USERNAME=###
DB_PASSWORD=###
DB_DATABASE=###

DB_SEARCH_PATH=public # only for PostgreSQL - database schema
```
After configuring database, run:

```sh
php artisan migrate
php artisan reverb:install
```

### Docker

Single container Docker version is planned

## Running
To launch WebSocket server for live preview, launch following tasks, and keep them running in background:

```
php artisan reverb:start
php artisan queue:work 
```

## Stack
- PHP8.2
- Laravel 11
- Vue 3.4 bridged with InertiaJS
- Laravel Echo for realtime updates
 