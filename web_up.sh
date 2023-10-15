#!/bin/bash
###
 # @Author: 18855190718 1491579574@qq.com
 # @Date: 2023-08-24 13:39:59
 # @LastEditors: 18855190718 1491579574@qq.com
 # @LastEditTime: 2023-10-08 07:55:39
 # @FilePath: \LTPP-CODE\web_up.sh
 # @Description: Email:1491579574@qq.com
 # QQ:1491579574
 # Copyright (c) 2023 by SQS, All Rights Reserved. 
###
scp -P 22 -rp -i C:\\Users\\14915\\.ssh\\LTPP\\id_rsa ./Frontend/frontend root@ltpp.vip:/home/LTPP/Frontend
echo "按回车键继续..."
read -n 1