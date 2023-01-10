FROM clickhouse/clickhouse-server:22.6-apline

# RUN apt-get update && apt-get install cron -y && apt-get install vim -y

RUN apk update && apk add --no-cache --update busybox-suid
RUN wget https://github.com/AlexAkulov/clickhouse-backup/releases/download/v1.5.2/clickhouse-backup-linux-amd64.tar.gz
RUN tar -xzf clickhouse-backup-linux-amd64.tar.gz
RUN cd build/linux/amd64/ && cp clickhouse-backup /bin/clickhouse-backup
RUN cd ~ && rm -rf clickhouse-backup-linux-amd64.tar.gz build

COPY ./cron /etc/crontabs/root
COPY ./backup.sh /var/lib/backup.sh
COPY ./entrypoint.sh /entrypoint.sh

RUN chmod 777 /etc/crontabs/root /var/lib/backup.sh /entrypoint.sh
