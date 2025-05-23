#!/bin/bash
###
 # @Author: root@ltpp.vip
 # @Date: 2023-08-22 13:31:27
 # @LastEditors: root@ltpp.vip
 # @LastEditTime: 2023-08-24 18:39:34
 # @FilePath: \LTPP-CODE\bin_up.sh
 # @Description: Email:1491579574@qq.com
 # QQ:1491579574
 # Copyright (c) 2023 by SQS, All Rights Reserved. 
###
# scp -P 22 -rp ./build/LTPP root@192.168.242.129:/home/LTPP
# scp -P 22 -rp -i C:\\Users\\14915\\.ssh\\128G\\id_rsa ./build/LTPP root@192.168.1.5:/home/LTPP
scp -P 40022 -rp -i C:\\Users\\14915\\.ssh\\128G\\id_rsa ./build/LTPP root@ltpp.vip:/tmp
echo "按回车键继续..."
read -n 1
