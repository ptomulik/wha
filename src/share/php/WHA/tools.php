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


require_once('WHA/misc.php');

$__wha_tools = array();

function wha_set_tool($name, $tool)
{
    global $__wha_tools;
    $__wha_tools[$name] = $tool;
}

function wha_tool($name)
{
    global $__wha_tools;
    if(isset($__wha_tools[$name])) return $__wha_tools[$name];
    else return FALSE;
}

/* --------------------------------------------------------------------------
 * wha_tools_init() 
 *
 *  Read configuration files.
 * -------------------------------------------------------------------------*/
function wha_tools_init(&$err = null, $file = 'tools.ini') 
{
    global $__wha_tools;

    if (is_file($file))
        $located = $file;
    else
        $located = wha_locate_wha_config($file);
    if ($located === FALSE) {
        $err = "can't locate file '" . $file . "'";
        return FALSE;
    }
    $ini = parse_ini_file($located, true);
    if ($ini === FALSE) {
        $err = "can't parse file '" . $located . "'";
        return FALSE;
    }

    if (!array_key_exists('tools', $ini)) {
        $err = "no section [tools] in '" . $located . "'";
        return FALSE;
    }

    $__wha_tools = $ini['tools'];

    return TRUE;
}


// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
