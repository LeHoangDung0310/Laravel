# Setting Up and Monitoring MySQL Database in Laravel

## Step 1: Configure the `.env` File
### Open the .env file in your Laravel project and add the following database settings:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```
## Step 2: Monitor the Database Connection
### Use Laravel Artisan to monitor the database connection:
#### --Write it in your project terminal.--
```bash
php artisan db:monitor
```
