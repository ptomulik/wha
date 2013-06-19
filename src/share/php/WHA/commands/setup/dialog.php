<?php
// Copyright (c) 2013 Pawel Tomulik <ptomulik@meil.pw.edu.pl>
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to 
// deal in the Software without restriction, including without limitation the 
// rights to use, copy, modify, merge, publish, distribute, sublicense, and/or 
// sell copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in 
// all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING 
// FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS 
// IN THE SOFTWARE


// Try to include PEAR::Config.
$__wha_garbage_21dad038 = error_reporting(0);
$__wha_garbage_e592e41b = include_once('Config.php');
error_reporting($__wha_garbage_21dad038);
if(!$__wha_garbage_e592e41b) {
    trigger_error('PEAR::Config is not installed.', E_USER_ERROR);
}
unset($__wha_garbage_e592e41b, $__wha_garbage_21dad038);

require_once('Config/Container.php');

require_once('WHA/misc.php');
require_once('WHA/dialog.php');

function wha_command_setup_dialog_apache_pkgname(&$root, $opts, $args)
{
    return 0;
}

function wha_command_setup_dialog_apache_confdir(&$root, $opts, $args)
{
    return 0;
}

function wha_command_setup_dialog_apache_moddir(&$root, $opts, $args)
{
    return 0;
}


function wha_command_setup_dialog_apache(&$root, $opts, $args)
{
    $handlers = array(
        'PACKAGE' => 'wha_command_setup_dialog_apache_pkgname',
        'CONFDIR' => 'wha_command_setup_dialog_apache_confdir',
        'MODDIR'  => 'wha_command_setup_dialog_apache_moddir'
    );
    $items = array(
        'PACKAGE' => 'Apache package name',
        'CONFDIR' => 'Apache configuration directory',
        'MODDIR'  => 'Apache modules directory',
        'RETURN'  => 'Return to main menu'
    );
    $text = "Choose action to take";
    $title = "Settings related to apache";
    $btitle = "WebHostAdm Setup";
    $dlg_opts = array('--title', $title, '--backtitle', $btitle, 
        '--default-item', 'PACKAGE');
    $done = FALSE;
    while(!$done) {
        $ans = wha_dialog_menu($out, $err, $text, 15, 60, 12, $items, $dlg_opts);
        switch ($ans) {
        case 0:
            $dlg_opts[5] = $out;
            if ($out == 'RETURN') {
                $done = TRUE;
            } else {
                $ans2 = call_user_func($handlers[$out], $root, $opts, $args);
                if($ans2 == -1) return -1;
            }
            break;
        case 1:
        case 255:
        default:
            $done = TRUE;
            break;
        default:
            if(is_string($err) && strlen($err) > 0) {
                $dlg_opts2 = array('--backtitle', $btitle, '--title',
                    "$title - error");
                wha_dialog_msgbox($err2, $err, 8, 60, $dlg_opts2);
            }
            $done = TRUE;
            break;
        }
    }
    return $ans;
}

function wha_command_setup_dialog_syslog_pkgname(&$root, $opts, $args)
{
    return 0;
}

function wha_command_setup_dialog_syslog_confdir(&$root, $opts, $args)
{
    return 0;
}

function wha_command_setup_dialog_syslog(&$root, $opts, $args)
{
    $handlers = array(
        'PACKAGE' => 'wha_command_setup_dialog_syslog_pkgname',
        'CONFDIR' => 'wha_command_setup_dialog_syslog_confdir',
    );
    $items = array(
        'PACKAGE' => 'System logger package name',
        'CONFDIR' => 'System logger configuration directory',
        'RETURN'  => 'Return to main menu'
    );
    $text = "Choose action to take";
    $title = "Settings related to syslog";
    $btitle = "WebHostAdm Setup";
    $dlg_opts = array('--title', $title, '--backtitle', $btitle, 
        '--default-item', 'PACKAGE');
    $done = FALSE;
    while(!$done) {
        $ans = wha_dialog_menu($out, $err, $text, 15, 60, 12, $items, $dlg_opts);
        switch ($ans) {
        case 0:
            $dlg_opts[5] = $out;
            if ($out == 'RETURN') {
                $done = TRUE;
            } else {
                $ans2 = call_user_func($handlers[$out], $root, $opts, $args);
                if($ans2 == -1) return -1;
            }
            break;
        case 1:
        case 255:
        default:
            $done = TRUE;
            break;
        default:
            if(is_string($err) && strlen($err) > 0) {
                $dlg_opts2 = array('--backtitle', $btitle, '--title',
                    "$title - error");
                wha_dialog_msgbox($err2, $err, 8, 60, $dlg_opts2);
            }
            $done = TRUE;
            break;
        }
    }
    return $ans;
}

function wha_command_setup_dialog_main($file, &$config, &$root, $opts, $args)
{

    $handlers = array(
        'APACHE' => 'wha_command_setup_dialog_apache',
        'SYSLOG' => 'wha_command_setup_dialog_syslog'
    );

    $items = array(
        'APACHE' => 'Settings related to apache',
        'SYSLOG' => 'Settings related to syslog',
        'COMMIT' => 'Save changes and exit'
    );
    $text = "Choose configuration step";
    $title = "Main configuration menu";
    $btitle = "WebHostAdm Setup";
    $dlg_opts = array('--title', $title, '--backtitle', $btitle, 
        '--default-item', 'APACHE');

    $done = FALSE;
    while(!$done) {
        $ans = wha_dialog_menu($out, $err, $text, 15, 60, 12, $items, $dlg_opts);
        switch ($ans) {
        case 0:
            $dlg_opts[5] = $out;
            if ($out == 'COMMIT') {
                // TODO: show differences
                // TODO: create directoriy if doesn't exists
                $res = $config->writeConfig($file, 'IniCommented');
                if($res !== TRUE) {
                    $dlg_opts2 = array('--backtitle', $btitle, '--title', 
                        "$title - error");
                    if(is_a($res, 'PEAR_Error')) {
                        wha_dialog_msgbox($err, $res->getMessage(), 6, 60, $dlg_opts2);
                    } else {
                        wha_dialog_msgbox($err, "Save failed!", 6, 20, $dlg_opts2);
                    }
                } else {
                    $done = TRUE;
                }
            } else {
                $ans2 = call_user_func($handlers[$out], $root, $opts, $args);
                if($ans2 == -1) return -1;
            }
            break;
        case 1:
        case 255:
        default:
            $done = TRUE;
            break;
        default:
            if(is_string($err) && strlen($err) > 0) {
                $dlg_opts2 = array('--backtitle', $btitle, '--title',
                    "$title - error");
                wha_dialog_msgbox($err2, $err, 8, 60, $dlg_opts2);
            }
            $done = TRUE;
            break;
        }
    }

    return $ans;
}

function wha_command_setup_dialog($script,$command,$common_opts,$opts,$args)
{
    global $wha_confdir;

    $file = wha_locate_wha_config('WHA.ini');
    if($file === FALSE) {
        if(!isset($wha_confdir) || !is_dir($wha_confdir)) {
            trigger_error("can't determine configuration directory", E_USER_ERROR);
        }
        $file = implode(DIRECTORY_SEPARATOR, array($wha_confdir, 'WHA.ini'));
    } 

    $root = null;
    $config = &new Config();
    if(is_file($file) && is_readable($file)) {
        $root = $config->parseConfig($file,'IniCommented');
        if(!is_a($root, 'Config_Container')) {
            $text = "Can't parse '" . $file . "' file. Please check it for syntax errors.";
            $ans = wha_dialog_msgbox($err, $text, 8, 60);
            return 1;
        }
    }

    if ($root==null) {
        $root = new Config_Container();
        $apache = $root->createSection('apache', array('pkgname' => '',
            'confdir' => '',
            'moddir' => '',
            'vhosts_available_dir' => '',
            'vhosts_enabled_dir' => '',
            'mods_available_dir' => '',
            'mods_enabled_dir' => ''));

        $newsyslog = $root->createSection('newsyslog', array('confdir' => '',
            'vhosts_confdir' => ''));
        $config->setRoot($root);
    }

    return wha_command_setup_dialog_main($file, $config, $root, $opts, $args);
}

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
