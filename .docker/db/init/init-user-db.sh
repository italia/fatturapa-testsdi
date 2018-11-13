#!/usr/bin/env bash

# Immediately exits if any error occurs during the script
# execution. If not set, an error could occur and the
# script would continue its execution.
set -o errexit

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" <<-EOSQL
    CREATE USER "www-data" WITH PASSWORD 'www-data';
    CREATE DATABASE testsdi OWNER "www-data";
EOSQL