#!/bin/sh
vapor team:switch --id 56463
mv vapor.yml vapor.bck
cp vapor-acc.yml vapor.yml
vapor deploy acc
