#!/bin/bash

LOCAL_DIR="/home/wwwroot/teanary.test/shared/storage/app/"
REMOTE_DIR="/home/wwwroot/teanary/shared/storage/app"
REMOTE_HOST="your.remote.ip.or.host"
REMOTE_USER="root"
SSH_KEY="/home/youruser/.ssh/id_rsa"  # 替换为你的私钥路径

# Rsync local to remote with SSH key
rsync -az -e "ssh -i ${SSH_KEY}" --delete "${LOCAL_DIR}" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/"

# Change owner and permission remotely
ssh -i ${SSH_KEY} ${REMOTE_USER}@${REMOTE_HOST} <<EOF
chown -R www:www ${REMOTE_DIR}
chmod -R 777 ${REMOTE_DIR}
EOF
