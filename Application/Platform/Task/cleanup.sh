#!/bin/sh
find /data/erp/source/Application/Runtime/Logs/ -mtime +365 -name "*.log" -exec rm -rf {} \;
find /data/erp/source/Application/Runtime/File/ -mtime +60 -iname "*Import*" -exec rm -rf {} \;