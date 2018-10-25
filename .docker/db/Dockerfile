FROM postgres

ENV POSTGRES_USER postgres
ENV POSTGRES_PASSWORD postgres

RUN apt-get update \
    && apt-get install -y --no-install-recommends nano

ADD ./init /docker-entrypoint-initdb.d/