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


require_once('WHA/Tools.php');

define('DIALOG_OK',0);
define('DIALOG_CANCEL',1);
define('DIALOG_HELP',2);
define('DIALOG_EXTRA',3);
define('DIALOG_ESC',255);

/**
 * @package WHA
 * @author P. Tomulik
 */
function wha_dialog_addslashes($str)
{
    if(preg_match('/^--[a-zA-Z][a-zA-Z0-9]*(-[a-zA-Z][a-zA-Z0-9]*)*$/',$str)) {
        return $str;
    } else {
        return '"'. addcslashes($str,"\"$") .'"';
    }
}

/**
 * Start the UNIX `dialog` program and return resource object representing the
 * new process.
 *
 * @param array options/arguments to dialog's CLI,
 * @param array pipes for communication with `dialog`,
 * @param file file descriptor to bind to dialog's STDIN,
 * @param file descriptor to redirect dialog's stdout to
 * @return resource resource representing new `dialog` process,
 *                  as returned by `proc_open()`
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_open($args, &$pipes, $stdin = STDIN, $stdout = STDOUT)
{
    $descriptorspec = array(
        0 => $stdin,
        1 => $stdout,
        2 => array("pipe", "w"),
        3 => array("pipe", "w")
    );

    $dialog = wha_tool('dialog');
    $noescape = ':^[a-zA-Z0-9_=-]+$:';
    $args = array_map(function($a) use ($noescape) {
        return preg_match($noescape,$a) ? $a : escapeshellarg($a);
    }, $args);
    $dialogcom = array_merge(array($dialog, '--output-fd', '3'), $args);
    $dialogcom = implode(' ', $dialogcom);
    return proc_open($dialogcom, $descriptorspec, $pipes);
}

/**
 * Wait for the `dialog` process to complete.
 *
 * Wait for `dialog` to complete and read its output and STDERR.
 *
 * @param resource  resource representing `dialog` process,
 *                  as returned by `proc_open()`
 * @param array     pipes for communication with `dialog`,
 * @param string    output produced by `dialog` command,
 * @param string    error messages produced by `dialog` command,
 * @return int      exit code from `dialog`, (more precise, the value returned
 *                  by `proc_close()`),
 *
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_close($process, $pipes, &$out=null, &$err=null)
{
    if(!is_resource($process)) {
        $err .= 'wha_dialog_close(): $process is not a resource';
        return -1;
    }

    if(isset($pipes[0])) {
        fclose($pipes[0]);
        usleep(2000);
    }

    if(isset($pipes[1])) {
        fclose($pipes[1]);
        usleep(2000);
    }

    $err = stream_get_contents($pipes[2]);
    $out = stream_get_contents($pipes[3]);

    fclose($pipes[2]);
    fclose($pipes[3]);

    $res = proc_close($process);
    if($res == -1) {
        if(!is_string($err)) $err = '';
        if(strlen($err) > 0) $err .= "\nerror: ";
        $err .= "wha_dialog_close(): proc_close() returned -1";
    }
    return $res;
}

/**
 * Execute the UNIX `dialog` command.
 *
 * @param array options/arguments to dialog's CLI,
 * @param string    output produced by `dialog` command,
 * @param string    error messages produced by `dialog` command,
 * @param file file descriptor to bind to dialog's STDIN,
 * @param file descriptor to redirect dialog's stdout to
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog($args, &$out=null, &$err=null, $stdin = STDIN,
    $stdout = STDOUT)
{
    $process = wha_dialog_open($args, $pipes, $stdin, $stdout);
    if($process === FALSE) {
        $out = '';
        $err = 'wha_dialog(): process initialization failed';
        return -1;
    }
    return wha_dialog_close($process, $pipes, $out, $err);
}

/**
 * Execute: `dialog ... --calendar ...`
 *
 * @param string output produced by calendar dialog,
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog`,
 * @param int dialog's height,
 * @param int dialog's width,
 * @param int day of month,
 * @param int month number,
 * @param int year,
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_calendar(&$out, &$err, $text, $height, $width,
    &$day, &$month, &$year, $common_options = array())
{
    $calendar_options = array('--calendar', $text, $height, $width, $day,
        $month, $year);
    $args = array_merge($common_options, $calendar_options);
    $result = wha_dialog($args, $out, $err);
    if($result == 0) {
        $matches = null;
        if(preg_match(':^([0-9]+)/([0-9]+)/([0-9]+)$:', $out, $matches)) {
            list($all,$day,$month,$year) = $matches;
        } else {
            $err = "wha_dialog_calendar(): internal error, can't parse calendar" .
                " output";
            return -1;
        }
    }
    return $result;
}

/**
 * Execute: `dialog ... --checklist ...`
 *
 * @param string output produced by calendar dialog,
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to dialog command,
 * @param int width argument to dialog command,
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_checklist(&$out, &$err, $text, $height, $width,
    $list_height, &$items, $common_options = array())
{
    $checklist_options = array('--checklist',$text,$height,$width,$list_height);
    $checklist_items = array();
    foreach($items as $key => $pair) {
        $checklist_items[] = $key;
        $checklist_items[] = $pair[0];
        $checklist_items[] = $pair[1];
    }
    if(!in_array('--separate-output', $common_options))
        $common_options[] = '--separate-output';
    $args = array_merge($common_options, $checklist_options, $checklist_items);
    $result = wha_dialog($args, $out, $err);
    if($result == 0) {
        if(is_string($out)) {
            $selection = explode("\n", $out);
            foreach(array_keys($items) as $key) {
                $items[$key][1] = in_array($key, $selection) ? 'on' : 'off';
            }
        }
    }
    return $result;
}

/**
 * Execute: `dialog ... --dselect ...`
 *
 * @param string output produced by calendar dialog,
 * @param string error messages produced by calendar dialog,
 * @param string filepath argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param array common options to `dialog` command,
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_dselect(&$out, &$err, $filepath, $height, $width,
    $common_options = array())
{
    $dselect_options = array('--dselect', $filepath, $height, $width);
    $args = array_merge($common_options, $dselect_options);
    $result = wha_dialog($args, $out, $err);
    return $result;
}

/**
 * Execute: `dialog ... --form ...`
 *
 * @param string output produced by calendar dialog,
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param int formheight argument to `dialog` command,
 * @param array items for the form `dialog`, must be array of 8-element arrays,
 * @param array common options to `dialog` command,
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_form(&$out, &$err, $text, $height, $width, $formheight,
    &$items, $common_options=array())
{
    $form_options = array('--form', $text, $height, $width, $formheight);
    $form_items = array();
    foreach($items as $key => $tuple) {
        $form_items[] = $tuple[0];  // label
        $form_items[] = $tuple[1];  // x
        $form_items[] = $tuple[2];  // y
        $form_items[] = $tuple[3];  // item
        $form_items[] = $tuple[4];  // x
        $form_items[] = $tuple[5];  // y
        $form_items[] = $tuple[6];  // flen
        $form_items[] = $tuple[7];  // ilen
    }
    $args = array_merge($common_options, $form_options, $form_items);
    $result = wha_dialog($args, $out, $err);
    if($result == 0) {
        if(is_string($out)) {
            $lines = explode("\n", $out);
            $i = 0;
            foreach($items as $key => $tuple) {
                $items[$key][3] = $lines[$i];
                $i++;
            }
        }
    }
    return $result;
}

/**
 * Execute: `dialog ... --fselect ...`
 *
 * @param string output produced by calendar dialog,
 * @param string error messages produced by calendar dialog,
 * @param string filepath argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param array common options to `dialog` command
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_fselect(&$out, &$err, $filepath, $height, $width,
    $common_options = array())
{
    $fselect_options = array('--fselect', $filepath, $height, $width);
    $args = array_merge($common_options, $fselect_options);
    $result = wha_dialog($args, $out, $err);
    return $result;
}


/**
 * Starts: `dialog ... --gauge ...`
 *
 * @param array pipes for communication with `dialog`,
 * @param file file descriptor to bind to dialog's STDIN,
 * @param string text argument to gauge dialog,
 * @param int heigh argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param int initial percentage,
 * @param array common options to `dialog` command
 * @return resource a resource representing newly created `dialog` process,
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_gauge_open(&$pipes, $stdin, $text, $height, $width,
    $percent = null, $common_options = array())
{
    $gauge_options = array('--gauge', $text, $height, $width);
    if($percent !== null) {
        $gauge_options[] = $percent;
    }
    $args = array_merge($common_options, $gauge_options);
    $process = wha_dialog_open($args, $pipes, $stdin);
    return $process;
}

/**
 * Closes: `dialog ... --gauge ...`
 *
 * @param
 * @param
 * @param
 * @param
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_gauge_close($process, $pipes, &$err=null)
{
    return wha_dialog_close($process, $pipes, $fake, $err);
}

/**
 * Execute: `dialog ... --infobox ...`
 *
 * @param string output produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param array common options to `dialog` command
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_infobox(&$err, $text, $height, $width,
    $common_options = array())
{
    $infobox_options = array('--infobox', $text, $height, $width);
    $args = array_merge($common_options, $infobox_options);
    $result = wha_dialog($args, $out, $err);
    return $result;
}

/**
 * Execute: `dialog ... --inputbox ...`
 *
 * @param string output produced by calendar dialog,
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param string initial string for the inputbox,
 * @param array common options to `dialog` command
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_inputbox(&$out, &$err, $text, $height, $width,
    $init = null, $common_options = array())
{
    $inputbox_options = array('--inputbox', $text, $height, $width);
    if($init !== null) {
        $inputbox_options[] = $init;
    }
    $args = array_merge($common_options, $inputbox_options);
    $result = wha_dialog($args, $out, $err);
    return $result;
}

/**
 * Execute: `dialog ... --inputmenu ...`
 *
 * @param string output produced by calendar dialog,
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param int menu_height argument to `dialog` command,
 * @param array items for the menu, must be associative array, keys serve as
 *              menuy tags and values as menu items
 * @param array common options to `dialog` command
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_inputmenu(&$out, &$err, $text, $height, $width,
    $menu_height, &$items, $common_options = array())
{
    $menu_options = array('--inputmenu', $text, $height, $width, $menu_height);
    $menu_items = array();
    foreach($items as $key => $val) {
        $menu_items[] = $key;
        $menu_items[] = $val;
    }
    $args = array_merge($common_options, $menu_options, $menu_items);
    $result = wha_dialog($args, $out, $err);
    if ($result == 3) {
        $matches = null;
        foreach ($items as $key => $item) {
            $regex = '/^\s*RENAMED\s'.preg_quote($key).'\s(.*)$/';
            if(preg_match($regex, $out, $matches)) {
                $items[$key] = $matches[1];
                break;
            }
        }
        if($matches === null) {
            // this shouldnt happen, if it does, then our regular expression is
            // probably wrong...
            $err = "wha_dialog_inputmenu(): internal error, don't know which item".
                " to rename";
            return -1;
        }
    }
    return $result;
}

/**
 * Execute: `dialog ... --menu ...`
 *
 * @param string output produced by calendar dialog,
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param int menu_height argument to `dialog` command,
 * @param array items for the menu, must be associative array, keys serve as
 *              menuy tags and values as menu items,
 * @param array common options to `dialog` command
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_menu(&$out, &$err, $text, $height, $width, $menu_height,
    $items, $common_options = array())
{
    $menu_options = array('--menu', $text, $height, $width, $menu_height);
    $menu_items = array();
    foreach($items as $key => $val) {
        $menu_items[] = $key;
        $menu_items[] = $val;
    }
    $args = array_merge($common_options, $menu_options, $menu_items);
    $result = wha_dialog($args, $out, $err);
    return $result;
}

/**
 * Execute: `dialog ... --mixedform ...`
 *
 * @param string output produced by calendar dialog,
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param int formheight argument to `dialog` command,
 * @param array items for the mixedform, must be an array of 9-element arrays,
 * @param array common options to `dialog` command
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_mixedform(&$out, &$err, $text, $height, $width,
    $formheight, &$items, $common_options=array())
{
    $form_options = array('--mixedform', $text, $height, $width, $formheight);
    $form_items = array();
    foreach($items as $key => $tuple) {
        $form_items[] = $tuple[0];  // label
        $form_items[] = $tuple[1];  // x
        $form_items[] = $tuple[2];  // y
        $form_items[] = $tuple[3];  // item
        $form_items[] = $tuple[4];  // x
        $form_items[] = $tuple[5];  // y
        $form_items[] = $tuple[6];  // flen
        $form_items[] = $tuple[7];  // ilen
        $form_items[] = $tuple[8];  // itype
    }
    $args = array_merge($common_options, $form_options, $form_items);
    $result = wha_dialog($args, $out, $err);
    if($result == 0) {
        if(is_string($out)) {
            $lines = explode("\n", $out);
            $i = 0;
            foreach($items as $key => $tuple) {
                $items[$key][3] = $lines[$i];
                $i++;
            }
        }
    }
    return $result;
}

/**
 * Execute: `dialog ... --mixedgauge ...`
 *
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param int percen argument to `dialog` command,
 * @param array items for the mixedgauge, must be an array of 2-element arrays,
 * @param array common options to `dialog` command
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_mixedgauge(&$err, $text, $height, $width, $percent,
    $items, $common_options = array())
{
    $mixedgauge_options = array('--mixedgauge', $text, $height, $width, $percent);
    $mixedgauge_items = array();
    foreach($items as $pair) {
        $mixedgauge_items[] = $pair[0];
        $mixedgauge_items[] = $pair[1];
    }
    $args = array_merge($common_options, $mixedgauge_options, $mixedgauge_items);
    $result = wha_dialog($args, $out, $err);
    return $result;
}

/**
 * Execute: `dialog ... --msgbox ...`
 *
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param array common options to `dialog` command
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_msgbox(&$err, $text, $height, $width,
    $common_options = array())
{
    $msgbox_options = array('--msgbox', $text, $height, $width);
    $args = array_merge($common_options, $msgbox_options);
    $result = wha_dialog($args, $out, $err);
    return $result;
}

/**
 * Execute: `dialog ... --pause ...`
 *
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param int seconds argument to `dialog` command,
 * @param array common options to `dialog` command,
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_pause(&$err, $text, $height, $width, $seconds,
    $common_options = array())
{
    $pause_options = array('--pause', $text, $height, $width, $seconds);
    $args = array_merge($common_options, $pause_options);
    $result = wha_dialog($args, $out, $err);
    return $result;
}

/**
 * Execute: `dialog ... --passwordbox ...`
 *
 * @param string output produced by calendar dialog,
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param string initial value of password for `dialog` command,
 * @param array common options to `dialog` command,
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_passwordbox(&$out, &$err, $text, $height, $width,
    $init = null, $common_options = array())
{
    $passwordbox_options = array('--passwordbox', $text, $height, $width);
    if($init !== null) {
        $passwordbox_options[] = $init;
    }
    $args = array_merge($common_options, $passwordbox_options);
    $result = wha_dialog($args, $out, $err);
    return $result;
}

/**
 * Execute: `dialog ... --passwordform ...`
 *
 * @param string output produced by calendar dialog,
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param int formheight argument to `dialog` command,
 * @param array items for the form `dialog`, must be array of 8-element arrays,
 * @param array common options to `dialog` command,
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_passwordform(&$out, &$err, $text, $height, $width,
    $formheight, &$items, $common_options=array())
{
    $form_options = array('--passwordform', $text, $height, $width, $formheight);
    $form_items = array();
    foreach($items as $key => $tuple) {
        $form_items[] = $tuple[0];  // label
        $form_items[] = $tuple[1];  // x
        $form_items[] = $tuple[2];  // y
        $form_items[] = $tuple[3];  // item
        $form_items[] = $tuple[4];  // x
        $form_items[] = $tuple[5];  // y
        $form_items[] = $tuple[6];  // flen
        $form_items[] = $tuple[7];  // ilen
    }
    $args = array_merge($common_options, $form_options, $form_items);
    $result = wha_dialog($args, $out, $err);
    if($result == 0) {
        if(is_string($out)) {
            $lines = explode("\n", $out);
            $i = 0;
            foreach($items as $key => $tuple) {
                $items[$key][3] = $lines[$i];
                $i++;
            }
        }
    }
    return $result;
}

/**
 * Execute: `dialog ... --radiolist ...`
 *
 * @param string output produced by calendar dialog,
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param int radiolist_height argument to `dialog` command,
 * @param array items for the form `dialog`, must be an associative array with
 *              2-element arrays as values,
 * @param array common options to `dialog` command,
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_radiolist(&$out, &$err, $text, $height, $width,
    $radiolist_height, $items, $common_options = array())
{
    $radiolist_options = array('--radiolist', $text, $height, $width,
        $radiolist_height);
    $radiolist_items = array();
    foreach($items as $key => $pair) {
        $radiolist_items[] = $key;
        $radiolist_items[] = $pair[0];
        $radiolist_items[] = $pair[1];
    }
    $args = array_merge($common_options, $radiolist_options, $radiolist_items);
    $result = wha_dialog($args, $out, $err);
    return $result;
}

/**
 * Execute: `dialog ... --timebox ...`
 *
 * @param string output produced by calendar dialog,
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param int hour number do `dialog` command,
 * @param int minute argument to `dialog` command,
 * @param int the `second` argument to `dialog` command,
 * @param array common options to `dialog` command,
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_timebox(&$out, &$err, $text, $height, $width = null,
    &$hour = null, &$min = null, &$sec = null,
    $common_options = array())
{
    $timebox_options = array('--timebox', $text, $height, $width, $hour,
        $min, $sec);
    $args = array_merge($common_options, $timebox_options);
    $result = wha_dialog($args, $out, $err);
    if($result == 0) {
        $matches = null;
        if(preg_match('/^([0-9]+):([0-9]+):([0-9]+)$/', $out, $matches)) {
            list($all,$hour,$min,$sec) = $matches;
        } else {
            $err = "wha_dialog_timebox(): internal error, can't parse timebox" .
                " output";
            return -1;
        }
    }
    return $result;
}

/**
 * Execute: `dialog ... --yesno ...`
 *
 * @param string error messages produced by calendar dialog,
 * @param string text argument to `dialog` command,
 * @param int height argument to `dialog` command,
 * @param int width argument to `dialog` command,
 * @param array common options to `dialog` command,
 * @return int  exit code form `dialog` command (more precise, a value returned
 *              by `proc_close()`).
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_dialog_yesno(&$err, $text, $height, $width,
    $common_options = array())
{
    $yesno_options = array('--yesno', $text, $height, $width);
    $args = array_merge($common_options, $yesno_options);
    $result = wha_dialog($args, $out, $err);
    return $result;
}



// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
