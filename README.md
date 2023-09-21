DSA Transparency Database [![Laravel](https://github.com/digital-services-act/transparency-database/actions/workflows/vapor.yml/badge.svg?branch=main)](https://github.com/digital-services-act/transparency-database/actions/workflows/vapor.yml)
=========================

The DSA Transparency Database collects the statements of reasons submitted by providers of online platforms to the
Commission, in accordance with Article 24(5) of the DSA to enable scrutiny over the content moderation decisions of the
providers of online platforms and to monitor the spread of illegal and other harmful content online.

Automated Submissions using an API
==================================

The [Transparency Database](https://transparency.dsa.ec.europa.eu/) has an API that allows providers of online platforms that issue large numbers of statements of
reasons to submit them without using the web interface. To learn about the capabilities of the API, you can consult the [API documentation](https://transparency.dsa.ec.europa.eu/page/api-documentation).

Search Using an API (not yet implemented, for future releases)
==============================================================

The Commission is considering a Search API that allows interested individuals, in particular from the research community, to extract large volumes of data from the database, in future releases of the database.

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
git clone https://github.com/digital-services-act/transparency-database
cd dsa-module2 && composer install 
```

### Step 2

Create `.env` based on `.env.example` file, and add your database credentials and the email that will be set as
administrator.

### Step 3

Create the database with the default values

### Step 4

Bootstrap the application

```bash
php artisan key:generate
chmod -R 777 storage
php artisan migrate:fresh --seed
php artisan reset-application
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

License
=======

DSA Transparency Database is licensed under GPLv2. See LICENSE.txt for more information.
