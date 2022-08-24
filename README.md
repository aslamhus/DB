# DB

A Database class for mysqli prepared statements. Has methods for MYSQL select, insert, update and delete.

## Install

```bash
composer require aslamhus/db
```

## Dependencies

1. Composer
2. composer package "vlucas/phpdotenv"
3. MYSQL database with access privileges
4. PHP

---

## Getting started

### Configuring your database

**DB** configures your database settings using environment variables. To set those up you'll need to install `vlucas/phpdotenv` and create a .env file with your database name, host, username and password.

#### 1. install vlucas/phpdotenv

```bash
composer require "vlucas/phpdotenv"
```

#### 2. create .env file in root directory with the following variables

```bash
// sets the development mode (possible values, "DEV" or "PRODUCTION")
DEV = "DEV"
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

Require your config.php file and then instantiate the DB class.

```php

require_once 'config.php'

use Database\DB;

$db = new DB();

```

That's it! You should now be connected to your database. You can begin making queries.

## Testing

To see `DB` in action you can run `test/index.php` in a local environemnt with mysql and php installed.

Before running these examples you'll need to set up a test database.

1. Login to your local mysql with admin privileges
2. create a test database/test table by executing test/db/schema.sql
3. add values to your test database by executing seeds.sql

Now you can run test/index.php in your local environment.

## Examples

### Select

`select` can take 3 parameters. The first parameter are the columns to select, the second is the tablename and the third optional parameter is an array of conditions.

#### Select everything

```php
$result = $db->select('*', 'tablename');
print_r($result);
```

#### Select with conditions

In this example we select everything from the table 'users' where a the username has a value of $username.

```php
$username = 'bob sacamano';
$result = $db->select('*','users',
    [
        'username = ?' => $username
    ]
);
print_r($result);
```

## Author

aslamhus
