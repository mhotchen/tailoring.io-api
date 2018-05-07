# Backend API for TMP

This is the API for the tailoring management platform.

The objective of the tailoring management platform is to provide a complete set of tools for tailors to manage their
business, such as customer and order management.

This API provides all of that functionality over REST which the Single Page Application (SPA) then consumes.

## Requirements for development:

* php 7.2 w/ composer, dom and mbstring extensions

## Running

```bash
cp .env.example .env
composer install
php artisan key:generate
docker-compose up
docker-compose up -d # run in background
```
