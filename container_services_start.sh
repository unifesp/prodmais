#!/usr/bin/env bash
/etc/init.d/elasticsearch start
service apache2 start

service apache2 status
service elasticsearch status