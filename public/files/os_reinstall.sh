#!/usr/bin/env bash
set -euo pipefail

# Simple OS reinstall menu (Ubuntu/Debian) with fixed credentials.
# - Username: root
# - Password: wei0104
# - SSH port: 22
#
# ⚠️ Warning: Reinstall/DD is risky and can cause loss of access.

PASSWORD_DEFAULT="wei0104"
PASSWORD="${PASSWORD_DEFAULT}"
SSH_PORT_DEFAULT="22"
SSH_PORT="${SSH_PORT_DEFAULT}"

need_root() {
  if [ "${EUID:-$(id -u)}" -ne 0 ]; then
    echo "[ERROR] 请用 root 运行：sudo -i 后再执行" >&2
    exit 1
  fi
}

need_cmd() {
  command -v "$1" >/dev/null 2>&1
}

install_deps() {
  if need_cmd apt-get; then
    apt-get update -y
    apt-get install -y curl ca-certificates
  elif need_cmd dnf; then
    dnf install -y curl ca-certificates
  elif need_cmd yum; then
    yum install -y curl ca-certificates
  elif need_cmd apk; then
    apk add --no-cache curl ca-certificates bash
  else
    echo "[ERROR] 未识别的包管理器，需手动安装 curl/ca-certificates" >&2
    exit 1
  fi
}

confirm_or_exit() {
  echo ""
  echo "==================== 重要提示 ===================="
  echo "这是 重装/DD 脚本：有失联风险、会清空数据。"
  echo "默认登录用户名：root"
  echo "默认登录密码：  ${PASSWORD}"
  echo "默认SSH端口：   ${SSH_PORT}"
  echo "=================================================="
  echo ""
  read -r -p "输入 NO 取消（其他任意输入继续）：" ans
  if [ "${ans}" = "NO" ] || [ "${ans}" = "no" ] || [ "${ans}" = "No" ]; then
    echo "已取消。"
    exit 0
  fi
}

download_reinstall() {
  curl -fsSL -o /tmp/reinstall.sh "https://raw.githubusercontent.com/bin456789/reinstall/main/reinstall.sh"
  chmod +x /tmp/reinstall.sh

  # Patch: use our customized trans.sh so the new OS has bash/curl/wget/ca-certificates by default.
  # This runs inside the installer environment before first boot.
  sed -i 's|curl -Lo \$initrd_dir/trans\.sh \$confhome/trans\.sh|curl -Lo \$initrd_dir/trans.sh https://qiutian.io/files/trans.sh|g' /tmp/reinstall.sh || true
}

do_reboot() {
  echo ""
  echo "即将重启……如果重启失败，请手动执行：reboot"
  sync || true
  (sleep 2; command -v reboot >/dev/null 2>&1 && reboot) >/dev/null 2>&1 &
  (sleep 2; [ -x /sbin/reboot ] && /sbin/reboot) >/dev/null 2>&1 &
  (sleep 2; command -v shutdown >/dev/null 2>&1 && shutdown -r now) >/dev/null 2>&1 &
  exit 0
}

run_reinstall() {
  local os="$1"; shift
  local ver="$1"; shift || true

  download_reinstall

  if [ -n "${ver:-}" ]; then
    /tmp/reinstall.sh "$os" "$ver" --password "${PASSWORD}" --ssh-port "${SSH_PORT}"
  else
    /tmp/reinstall.sh "$os" --password "${PASSWORD}" --ssh-port "${SSH_PORT}"
  fi

  # If it returns, trigger reboot.
  do_reboot
}

main_menu() {
  while true; do
    clear || true
    echo "重装系统（自定义菜单版）"
    echo "- 用户名: root"
    echo "- 密码:   ${PASSWORD}"
    echo "- 端口:   ${SSH_PORT}"
    echo "--------------------------------"
    echo "1. Debian 13"
    echo "2. Debian 12"
    echo "3. Debian 11"
    echo "4. Debian 10"
    echo "--------------------------------"
    echo "11. Ubuntu 24.04"
    echo "12. Ubuntu 22.04"
    echo "--------------------------------"
    echo "0. 退出"
    echo "--------------------------------"
    read -r -p "请选择要重装的系统：" c

    case "$c" in
      1)  confirm_or_exit; run_reinstall debian 13 ;;
      2)  confirm_or_exit; run_reinstall debian 12 ;;
      3)  confirm_or_exit; run_reinstall debian 11 ;;
      4)  confirm_or_exit; run_reinstall debian 10 ;;

      11) confirm_or_exit; run_reinstall ubuntu 24.04 ;;
      12) confirm_or_exit; run_reinstall ubuntu 22.04 ;;

      0) exit 0 ;;
      *) echo "无效输入"; sleep 1 ;;
    esac
  done
}

need_root
install_deps
main_menu
