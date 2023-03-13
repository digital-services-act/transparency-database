DSA Transparency Database [![Laravel](https://github.com/DG-CNECT/dsa-module2/actions/workflows/vapor.yml/badge.svg?branch=main)](https://github.com/DG-CNECT/dsa-module2/actions/workflows/vapor.yml)
=========================

The DSA Transparency database collects and analyzes statements of reasons, helping Internet users to know their rights and understand the law. These data enable us to study the prevalence of legal threats and let Internet users see the source of content removals.

Automated Submissions and Search Using the API
==============================================
The main [Transparency Database](https://digital.service-act.eu/) has an API that allows individuals and organizations that receive large numbers of notices to submit them without using the web interface. The API also provides an easy way for researchers to search the database. Members of the public can test the database, but will likely need to request an API key from the DSA team to receive a token that provides full access. To learn about the capabilities of the API, you can consult the [API documentation](https://wandering-mountain-xa4vca0rl0ff.vapor-farm-g1.com/dashboard/page/api-documentation).

Development
===========

#### Stack

* php 8.1
* Mysql 8

#### Pre-requisites

* [Composer](https://getcomposer.org/)

#### Setup

### Step 1

Begin by cloning this repository to your machine, and installing Composer dependencies.

```bash
git clone https://github.com/DG-CNECT/dsa-module2
cd dsa-module2 && composer install 
```

### Step 2

Create `.env` based on `.env.example` file, and add your database credentials and the email that will be set as administrator.


### Step 3

Create the database with the default values

### Step 4

Bootstrap the application

```bash
php artisan key:generate
chmod -R 777 storage
php artisan migrate:fresh --seed
```

#### Running the app

```bash
php artisan serve
```

#### Viewing the app

```
$BROWSER 'http://127.0.0.1:8000'
```

#### Running Tests

    $ php artisan test

#### Parallelizing Tests

You can speed up tests by running them in parallel:

    $ php artisan test --parallel

DSA Transparency API
====================
You can search the database and, if you have a contributor token, add to the database using our API.

The DSA Transparency API is documented here: https://wandering-mountain-xa4vca0rl0ff.vapor-farm-g1.com/dashboard/page/api-documentation

License
=======

DSA Database is licensed under GPLv2. See LICENSE.txt for more information.
