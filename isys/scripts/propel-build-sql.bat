@echo off

php ../../vendor/propel/propel/bin/propel sql:build --config-dir="../../orm" --schema-dir="../../orm/schema" --output-dir="../../orm/sql/generated" --overwrite
