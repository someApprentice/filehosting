# filehosting

Simple service for sharing files

Implemented via:
* Slim micro framework https://www.slimframework.com/
* Doctrine ORM http://www.doctrine-project.org/projects/orm.html
* Twig https://twig.symfony.com/
* Twitter Bootstrap https://getbootstrap.com/

## Requirements
* PHP5.6 or above
* PostgreSQL
* SphinxSearch
* Composer

## Installation
1. `git clone https://github.com/someApprentice/filehosting.git`
1. create new database and import dump file (`filehosting.sql` in the work directory)
1. `composer install`
1. change database configuraion in the `/config/config.ini` file
1. change sphinx configuration in the `/config/sphinx.conf` file
1. create sphinx indexes `indexer --config /config/sphinx.conf --all`
1. run SphinxSearch service `searchd --config /config/sphinx.conf`
