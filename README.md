# DB

A Database class for mysqli prepared statements. Has methods for MYSQL select, insert, update and delete.

## Install

```bash
composer require aslamhus/db
```

## Dependencies

1. composer
2. composer package "vlucas/phpdotenv"

---

## Getting started

### Configuration your database

In order to connect to your database, you'll need to set your database configuration in env variables.

#### 1. install vlucas/phpdotenv

```bash
composer require "vlucas/phpdotenv"
```

#### 2. create .env file in root directory with the following variables

```bash
// sets the development mode (possible values, "DEV" or
DEV = "DEV" "PRODUCTION")
// your db hostname
DB_HOST = "localhost"
// your db name
DB_NAME = "db_name"
// your db user name
DB_USER = "db_user"
// your db password
DB_PASS = "db_password"
// your local db password
DB_LUSER = "local_db_user"
// your local db username
DB_LNAME = "local_db_password"
```

#### 3. load your .env variables

create a config.php file with the following:

```php
// require composer autoload
require_once '../vendor/autoload.php';
// path to your .env file
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__), '.env');
// load the variables
$dotenv->load() ;
// ensure the correct env variables are loaded
$dotenv->required(['DEV', 'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_LUSER', 'DB_LNAME'])->notEmpty();
$dotenv->required('DEV')->allowedValues(['DEV', 'PRODUCTION']);
```

More information on [dotenv](https://github.com/vlucas/phpdotenv)

### Connect to the database

Require your config.php file and then invoke the DB class.

```php

require_once 'config.php'

use DB;

$db = new DB();

```

That's it! You should now be connected to your database. You can begin making queries.

## Examples

### Select

```php
$result = $db->select('*', 'tablename');
```

## Author

aslamhus
