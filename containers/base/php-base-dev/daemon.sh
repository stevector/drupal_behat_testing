#!/bin/bash

terminate() {
  echo "Container terminated"
  exit 0
}

trap terminate SIGHUP SIGINT SIGTERM

while true;
  do
  sleep 500 & wait
done
