#!/bin/bash
cur_dir=$(cd "$(dirname "$0")"; pwd)
ps -eaf |grep "RPC customer Server" | grep -v "grep"| awk '{print $2}'|xargs kill -9
sleep 1
cd $cur_dir
cd ..
php customer_server.php
