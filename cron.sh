#!/bin/bash

while true; do
  php artisan communicate
  php artisan track
  sleep 5;
done
