#!/bin/bash
echo '[1/5] 安装依赖...'
apt update && apt install -y curl tar
echo '[2/5] 安装 3x-ui...'
echo 'n' | bash <(curl -Ls https://raw.githubusercontent.com/MHSanaei/3x-ui/master/install.sh)
echo '[3/5] 强制修改配置...'
sleep 2
RAND_PATH=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 3 | head -n 1)
/usr/local/x-ui/x-ui setting -username admin -password admin -port 2053 -webBasePath "${RAND_PATH}" -webCert "" -webCertKey ""
echo '[4/5] 重启服务...'
systemctl restart x-ui
echo '=========================================='
echo '安装完成！'
echo "面板地址: http://$(curl -s ipv4.icanhazip.com):2053/${RAND_PATH}/"
echo '用户名:   admin'
echo '密码:     admin'
echo '=========================================='
