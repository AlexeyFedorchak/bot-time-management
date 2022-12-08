#!/bin/bash

while true; do
  php artisan communicate
  php artisan track
  php artisan register
  sleep 5;
done
