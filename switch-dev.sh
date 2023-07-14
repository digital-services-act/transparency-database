#!/bin/sh
vapor team:switch --id 48259
mv vapor.yml vapor.bck
cp vapor-dev.yml vapor.yml
