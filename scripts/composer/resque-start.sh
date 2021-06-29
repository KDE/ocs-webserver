#!/bin/sh

echo "start resque worker"
vendor/bin/resque worker:start -c ./config/resque.config.yml -vvv &
