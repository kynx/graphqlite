ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}
ARG GRAPHQLITE_VERSION=v0.3.2
ARG GRAPHQLITE_SHA256=24f5bcf2f29f7f1cf3684cf812ed7c891d751d9103e1b82942f076a4b4b0a2e5

RUN apt update \
    && mkdir -p /opt/local/lib/sqlite \
    && curl -L -o /opt/local/lib/sqlite/graphqlite.so \
        https://github.com/colliery-io/graphqlite/releases/download/${GRAPHQLITE_VERSION}/graphqlite-linux-x86_64.so \
    && echo "${GRAPHQLITE_SHA256} /opt/local/lib/sqlite/graphqlite.so" | sha256sum -c

ENV GRAPHQLITE_EXTENSION_PATH=/opt/local/lib/sqlite/graphqlite.so
