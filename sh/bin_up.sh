#!/bin/bash
# Build host -> production host single-binary deploy.
# Edit the host, port, and ssh key path below for your environment.
scp -P 40022 -rp -i C:\\Users\\14915\\.ssh\\128G\\id_rsa ./build/LTPP root@120.53.248.2:/tmp
echo "按回车键继续..."
read -n 1
