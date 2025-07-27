#!/bin/bash

LOCAL_DIR="/home/wwwroot/teanary.test/shared/storage/app/"
REMOTE_DIR="/home/wwwroot/teanary/shared/storage/app"
REMOTE_HOST="your.remote.ip.or.host"
REMOTE_USER="root"

# Rsync local to remote
rsync -az --delete "${LOCAL_DIR}" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/"

# Change owner and permission remotely
ssh ${REMOTE_USER}@${REMOTE_HOST} <<EOF
chown -R www:www ${REMOTE_DIR}
chmod -R 777 ${REMOTE_DIR}
EOF