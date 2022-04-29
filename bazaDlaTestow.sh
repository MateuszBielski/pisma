#!/bin/bash

composer update
echo 'DATABASE_URL="mysql://user:pasword@127.0.0.1:3306/db_pisma_test?serverVersion=8.0"' > .env.test.local
echo "zmień użytkownika i hasło w .env.test.local";

php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:schema:create
php bin/console make:fixtures

php bin/console --env=test doctrine:fixtures:load