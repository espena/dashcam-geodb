FROM postgis/postgis:14-3.2
RUN apt update && apt install -y gdal-bin
COPY create.sql /docker-entrypoint-initdb.d/create.sql
