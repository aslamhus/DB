# DB

A Database class for mysqli prepared statements. Connect to your database and easily prepare statements, bind variables and execute queries. Currently Has methods for MYSQL select, insert, update and delete.

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

#### 1. Install

If you have cloned the repo, install all dependencies.

```bash
composer install
```

If you are installing `@aslamhus/db` as a package,
simply run

```bash
composer require @aslamhus/db
```

You should now have `vlucas/phpdotenv` in your vendor directory.
More information on [vlucas/dotenv](https://github.com/vlucas/phpdotenv)

#### 2. Create .env file in your project root directory with the following variables

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

#### 3. Load your environment variables

create a config.php file with the following:

**Make sure you specify your path to vendor/autload and your .env file**

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

#### 4. Connect to the database

Require your config.php file and then instantiate the DB class.

```php

require_once 'config.php'

use Database\DB;

$db = new DB();

```

That's it! You should now be connected to your database. You can begin making queries.

### Optional: Allowlist for table names and columns

If you would like to add an extra layer of security, you can create an allow list of valid table names and valid column names in your mysql queries.

Create two text files in the src directory, '.validcolumns' and '.validtables'
Separate names of valid tables and valid columns by line breaks.
`DB` will then only perform queries on valid columns or tables.
If the files are left blank or do not exist, all tables and all columns will be allowed.

## Testing

To see `DB` in action you can run `test/index.php` in a local environment with mysql and php installed.

Before running these examples you'll need to set up a test database.

1. Login to your local mysql with admin privileges
2. create a test database/test table by executing test/db/schema.sql
3. add values to your test database by executing seeds.sql

Now you can run test/index.php in your local environment.

## Examples

### `select`

`select` takes two parameters with an optional third. The first parameter is the columns to select, the second is the tablename and the third is an optional array of conditions.

#### Select everything

```php
$result = $db->select('*', 'tablename');
print_r($result);
```

#### Select with conditions

In this example we select everything from the table 'users' where a the username has a value of the variable $username.

```php
$username = 'bob sacamano';
$result = $db->select('*','users',
    [
        'username = ?' => $username
    ]
);
print_r($result);
```

... more examples to come!

## Author

aslamhus
