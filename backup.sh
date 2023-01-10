#!/bin/bash
BACKUP_NAME=$BACKUP_PRE-$(date -u +%Y-%m-%dT%H-%M-%S)
clickhouse-backup create
if [[ $? != 0 ]]; then
  echo "clickhouse-backup create $BACKUP_NAME FAILED and return $? exit code"
fi