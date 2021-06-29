#!/bin/sh

echo "stop resque worker"
vendor/bin/resque worker:stop -c ./config/resque.config.yml -vvv &
