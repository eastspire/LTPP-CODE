#!/bin/bash
###
 # @Author: 1491579574@qq.com
 # @Date: 2023-08-22 13:31:27
 # @LastEditors: 1491579574@qq.com
 # @LastEditTime: 2023-08-24 18:39:34
 # @FilePath: \LTPP-CODE\bin_up.sh
 # @Description: Email:1491579574@qq.com
 # QQ:1491579574
 # Copyright (c) 2023 by SQS, All Rights Reserved. 
###
scp -P 22 -rp ./build/LTPP root@192.168.242.129:/home/LTPP
echo "按回车键继续..."
read -n 1
