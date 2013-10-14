#!/bin/bash
echo "DROP DATABASE investiclub" | mysql -u root -ptoto
echo "CREATE DATABASE investiclub" | mysql -u root -ptoto
mysql -u root -ptoto investiclub < /var/www/ivc/scripts/db/mysql/investiclub.sql
