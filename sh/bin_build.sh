#!/bin/bash
###
 # @Author: root@ltpp.vip
 # @Date: 2023-08-22 13:31:27
 # @LastEditors: root@ltpp.vip
 # @LastEditTime: 2023-08-24 18:33:06
 # @FilePath: \LTPP-CODE\bin_build.sh
 # @Description: Email:root@ltpp.vip
 # QQ:1491579574
 # Copyright (c) 2023 by SQS, All Rights Reserved. 
###
php -d phar.readonly=0 webman build:bin 8.2
php -d phar.readonly=0 webman build:bin 8.2
gtl acp
# ./sh/bin_up.sh
cp ./build/LTPP ../LTPP
cd ../LTPP
gtl acp
