FROM postgres:12

RUN apk update && apk get install s3cmd -y
