FROM postgres:12

RUN apt-get update && apt-get install s3cmd -y
