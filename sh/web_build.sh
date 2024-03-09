#!/bin/bash
###
 # @Author: 18855190718 1491579574@qq.com
 # @Date: 2023-08-22 13:31:27
 # @LastEditors: wmzn-ltpp 1491579574@qq.com
 # @LastEditTime: 2023-12-30 19:03:34
 # @FilePath: \LTPP-CODE\web_build.sh
 # @Description: Email:1491579574@qq.com
 # QQ:1491579574
 # Copyright (c) 2023 by SQS, All Rights Reserved. 
###
cd ./Frontend
npm run build
npm run build
cd ../sh
./web_up.sh