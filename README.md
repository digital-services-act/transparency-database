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

* php 8.4
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

Create a local database

### Step 3

Create `.env` based on `.env.example` file, and add your database credentials and the email that will be set as
administrator.

### Step 4

Bootstrap the application

```bash
php artisan key:generate
chmod -R 777 storage
php artisan migrate:fresh --seed
php artisan reset-application
```

#### Running the app

    $ php artisan serve

#### Viewing the app

```
$BROWSER 'http://127.0.0.1:8000'
```

#### Running Tests

    $ php artisan test

#### Parallelize Tests

You can speed up tests by running them in parallel:

    $ php artisan test --parallel

#### Login and Authentications

To use the authenticated parts of the application you will need to have an EU login account.

Additionally, your local installation will need to be hosted on a domain that ends with "europa.eu".

ex, https://transparency.test.europa.eu

#### API token lookup cache

Sanctum token records are cached for a fixed five minutes in the Redis cache store.
Configure the existing `REDIS_*` connection settings and keep `SANCTUM_CACHE_STORE=redis`
on every application instance. This store is independent of `CACHE_DRIVER`; automated
tests use the array store instead. Redis must be available for bearer authentication:
cache errors fail the request rather than bypassing authentication.

Only token attributes are cached. Sanctum still verifies the secret and expiration,
and user and permission data remain fresh. Cache hits do not extend the five-minute
lifetime, and entries never outlive the token's configured expiration.

Token changes and individual model deletions invalidate cached lookups. The profile
token reset and API user deletion paths delete tokens individually for this reason.
Future revocation code must also delete model instances: bulk SQL/Eloquent deletions
bypass model events. An already-running cache miss can refill an entry after deletion;
the fixed lifetime bounds this race to five minutes from the start of the lookup.

License
=======

DSA Transparency Database is licensed under GPLv2. See LICENSE.txt for more information.

Assistance
=======
For any type of issues please contact: CNECT-DSA-HELPDESK@ec.europa.eu
