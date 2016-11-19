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


function wha_list_clicmd_files()
{
    $files = array();
    $subdir = implode(DIRECTORY_SEPARATOR, array('WHA', 'CliCmds'));
    $dirlist = explode(PATH_SEPARATOR, get_include_path());
    foreach($dirlist as $dir) {
        $dir = implode(DIRECTORY_SEPARATOR, array($dir, $subdir));
        if(is_dir($dir)) {
            $glob = implode(DIRECTORY_SEPARATOR, array($dir, '*.php'));
            $res = glob($glob);
            if($res === FALSE) continue;
            $res = array_filter($res, 'is_file');
            $files = array_merge($files, $res);
        }
    }
    $files = array_map('basename', $files);
    $files2 = array();
    foreach($files as $f) {
        $files2[] = implode(DIRECTORY_SEPARATOR, array($subdir, $f));
    }
    return $files2;
}

foreach(wha_list_clicmd_files() as $file) {
    require_once($file); // and they should register themselves ...
}

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
