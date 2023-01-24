#!/bin/bash
BACKUP_NAME=$BACKUP_$(date -u +%Y-%m-%d)
clickhouse-backup create_remote
if [[ $? != 0 ]]; then
  echo "clickhouse-backup create $BACKUP_NAME FAILED and return $? exit code"
fi