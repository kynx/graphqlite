#!/usr/bin/env bash

GRAPHQLITE_VERSION="v0.3.2"
GRAPHQLITE_SHA256=24f5bcf2f29f7f1cf3684cf812ed7c891d751d9103e1b82942f076a4b4b0a2e5

WORKDIR="$2"

export GRAPHQLITE_EXTENSION_PATH="${WORKDIR}/graphqlite.so"
curl -L -o "${GRAPHQLITE_EXTENSION_PATH}" \
  "https://github.com/colliery-io/graphqlite/releases/download/${GRAPHQLITE_VERSION}/graphqlite-linux-x86_64.so"
echo "${GRAPHQLITE_SHA256} ${GRAPHQLITE_EXTENSION_PATH}" | sha256sum -c

