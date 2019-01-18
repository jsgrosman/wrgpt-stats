FROM mysql:5.7

COPY ./sql-init/* /docker-entrypoint-initdb.d/



