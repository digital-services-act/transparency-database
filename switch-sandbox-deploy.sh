#!/bin/sh
vapor team:switch --id 56463
mv vapor.yml vapor.bck
cp vapor-sandbox.yml vapor.yml
