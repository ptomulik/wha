#! /bin/sh
##############################################################################
# !!!DO NOT EDIT!!! - your changes will be overridden.
#
# To tweak configuration manually, put your variable definitions to 
# apache.conf.local.
#
# Webhostadm configuration file: apache-related information.
##############################################################################

##############################################################################
# <?php echo $annotations->get_open_tag()."\n" ?>
# uuid:         <?php echo $annotations->get_uuid()."\n"; ?>
# file_type:    <?php echo $annotations->get_filetype()."\n"; ?>
# created:      <?php echo $annotations->get_created()."\n"; ?>
# created_by:   <?php echo $annotations->get_created_by()."\n"; ?>
# modified:     <?php echo $annotations->get_modified()."\n"; ?>
# modified_by:  <?php echo $annotations->get_modified_by()."\n"; ?>
# <?php echo $annotations->get_close_tag()."\n" ?>
##############################################################################

##############################################################################
# WHA_APACHE_PKGNAME
#
# TODO: write documentation
##############################################################################
WHA_APACHE_PKGNAME="<?php echo $conf->get_var('APACHE_PKGNAME'); ?>";

##############################################################################
# WHA_APACHE_CONF_DIR
#
# TODO: write documentation
##############################################################################
WHA_APACHE_CONF_DIR="<?php echo $conf->get_var('APACHE_CONF_DIR'); ?>";

##############################################################################
# WHA_APACHE_MOD_DIR
#
# TODO: write documentation
##############################################################################
WHA_APACHE_MOD_DIR="<?php echo $conf->get_var('APACHE_MOD_DIR'); ?>";

##############################################################################
# WHA_APACHE_VHOSTS_CONF_DIR
#
# TODO: write documentation
##############################################################################
WHA_APACHE_VHOSTS_CONF_DIR="<?php echo $conf->get_var('APACHE_VHOSTS_CONF_DIR'); ?>";

##############################################################################
# WHA_APACHE_NEWSYSLOG_CONF_DIR
#
# TODO: write documentation
##############################################################################
WHA_APACHE_NEWSYSLOG_CONF_DIR="<?php echo $conf->get_var('APACHE_NEWSYSLOG_CONF_DIR'); ?>";

##############################################################################
# WHA_APACHE_VHOSTS_NEWSYSLOG_CONF_DIR
#
# TODO: write documentation
##############################################################################
WHA_APACHE_VHOSTS_NEWSYSLOG_CONF_DIR="<?php $conf->get_var('APACHE_VHOSTS_NEWSYSLOG_CONF_DIR'); ?>";

# vim: set syntax=sh:
