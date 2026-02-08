#!/usr/bin/env bash
set -e -x -o pipefail

GRAPHQLITE_VERSION="v0.3.3"
GRAPHQLITE_SHA256=108f9e5c8b51be574a16ce27caa4cbf3ebbd9c8411cedf7fdd777ab296e3aeb6

WORKDIR=$2
JOB=$3
COMMAND=$(echo "${JOB}" | jq -r '.command')
if [[ ! ${COMMAND} =~ phpunit ]];then
    exit 0
fi

export GRAPHQLITE_EXTENSION_PATH="${WORKDIR}/graphqlite.so"
curl -L -o "${GRAPHQLITE_EXTENSION_PATH}" \
  "https://github.com/colliery-io/graphqlite/releases/download/${GRAPHQLITE_VERSION}/graphqlite-linux-x86_64.so"
echo "${GRAPHQLITE_SHA256} ${GRAPHQLITE_EXTENSION_PATH}" | sha256sum -c

cd "${WORKDIR}"
sed -e "s#name=\"GRAPHQLITE_EXTENSION_PATH\" value=\"\"#name=\"GRAPHQLITE_EXTENSION_PATH\" value=\"${GRAPHQLITE_EXTENSION_PATH}\"#" \
  phpunit.xml.dist > phpunit.xml
