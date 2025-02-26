#!/bin/sh
set -e

if [ -f /custom-files/etc/msmtprc ]; then
  cp -f /custom-files/etc/msmtprc /etc/msmtprc
  echo "Custom /etc/msmtprc configuration file was successfully copied from /custom-files/etc/msmtprc."
  chmod 0600 /etc/msmtprc
  echo "Config /etc/msmtprc mod changed to 0600."
fi
