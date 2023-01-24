FROM clickhouse/clickhouse-server:22.6-alpine

RUN apk update && apk add --no-cache --update busybox-suid
RUN wget https://github.com/AlexAkulov/clickhouse-backup/releases/download/v1.5.2/clickhouse-backup-linux-amd64.tar.gz
RUN tar -xzf clickhouse-backup-linux-amd64.tar.gz
RUN cd build/linux/amd64/ && cp clickhouse-backup /bin/clickhouse-backup
RUN cd ~ && rm -rf clickhouse-backup-linux-amd64.tar.gz build

COPY ./backup.sh /var/lib/backup.sh
COPY ./entrypoint.sh /entrypoint.sh

RUN chmod 777 /var/lib/backup.sh /entrypoint.sh
