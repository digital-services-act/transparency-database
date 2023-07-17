#!/bin/sh
vapor team:switch --id 56452
mv vapor.yml vapor.bck
cp vapor-prod.yml vapor.yml
