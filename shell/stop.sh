#!/bin/bash
ps -eaf |grep "RPC customer Server" | grep -v "grep"| awk '{print $2}'|xargs kill -9