#! /bin/sh

# The Docker Minio Container's entrypoint.
# This is a script used by the project's Docker development environment to
# setup development buckets:.

# 1: Create the 'demo' bucket:
mkdir -p /data/demo

# 2: Invoke the original entrypoint:
exec /usr/bin/docker-entrypoint.sh "$@"