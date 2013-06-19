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

$wha_default_confdirs = array(
    '/usr/local/etc/wha',
    '/etc/wha'
);

/**
 * Lookup typical location for a configuration file. Return the name of the 
 * first file found or `false` if file was not found.
 *
 * @basename string basename of the config file, e.g. `"wha.ini"`,
 * @return string|false
 * @package WHA
 * @since 0.1
 */
function wha_locate_wha_config($basename)
{
    global $wha_confdir; 
    global $wha_default_confdirs;


    $confdirs = array();
    if (isset($wha_confdir)&&is_string($wha_confdir)&&is_dir($wha_confdir)) {
        $confdirs[] = $wha_confdir;
    }
    array_merge($confdirs, $wha_default_confdirs);

    foreach($confdirs as $confdir) {
        $file = implode(DIRECTORY_SEPARATOR, array($confdir, $basename));
        if(is_file($file))
            return $file;
    }
    return false;
}

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
