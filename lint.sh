#!/usr/bin/env bash

alias composer='docker run --rm -it -v "$(pwd):/app" composer/composer'

action=$1

case $action in
"check")
  composer update
  composer install
  ./vendor/bin/phpcs -s omnisend
  ;;
"fix")
  composer update
  composer install
  ./vendor/bin/phpcbf omnisend
  ;;
*)
  echo "pass one of these argument: check,fix"
  exit 1
  ;;
esac
