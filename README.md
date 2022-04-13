# Project used as an example for [Laravel Multi ENVs](https://github.com/allysonsilva/laravel-multienv)

[![CI - Tests Envs](https://github.com/allysonsilva/laravel-multienv-use/actions/workflows/main.yml/badge.svg)](https://github.com/allysonsilva/laravel-multienv-use/actions/workflows/main.yml)

> This project is used to run dusk tests using the [`laravel-multienv`](https://github.com/allysonsilva/laravel-multienv-use) package.

## Running The Tests Locally:

To see which version/branch to run, see the table below:

**Laravel version Compatibility**

| Laravel |  PHP  | Version of `laravel-multienv` | Branch |
|:-------:|:-----:|:------------:|:------------:|
|   9.x   | >=8.0 |   **^2.0**   |   **2.x**   |
|   8.x   | >=7.4 |   **^1.0**   |   **1.x**   |

*To run the tests locally, follow these steps*:

```bash
# Prepare The Environment
cp .env.example .env

# Install Composer Dependencies
composer update --prefer-stable --prefer-dist --no-interaction --ansi --optimize-autoloader

# Generate Application Key
php artisan key:generate

# Upgrade Chrome Driver
php artisan dusk:chrome-driver

# Add hosts to /etc/hosts
sudo echo "127.0.0.1 site1.test" | sudo tee -a /etc/hosts
sudo echo "127.0.0.1 site2.test" | sudo tee -a /etc/hosts
sudo echo "127.0.0.1 env-a.test" | sudo tee -a /etc/hosts
sudo echo "127.0.0.1 env-b.test" | sudo tee -a /etc/hosts
sudo echo "127.0.0.1 env-c.test" | sudo tee -a /etc/hosts

# Run Laravel Server
nohup php artisan serve --host=site1.test --port=8010 &
nohup php artisan serve --host=site2.test --port=8020 &
nohup php artisan serve --host=env-a.test --port=8001 &
nohup php artisan serve --host=env-b.test --port=8002 &
nohup php artisan serve --host=env-c.test --port=8003 &

# Run Dusk Tests
php artisan dusk
```
