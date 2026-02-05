FROM php:8.4 AS builder

RUN apt update \
    && apt-get install -y build-essential bison flex git libsqlite3-dev \
    && git clone https://github.com/colliery-io/graphqlite.git \
    && cd graphqlite \
    && make extension RELEASE=1

FROM php:8.4

COPY --from=builder /graphqlite/build/graphqlite.so /usr/local/lib/sqlite/extensions/

