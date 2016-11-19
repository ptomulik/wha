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


require_once('WHA/shell_config_file_annotations.php');

$annotations = new WHA_ShellConfigFileAnnotations();
$in = fopen('php://stdin', 'r');
$text = "";
while (!feof($in)) $text = $text . fgets($in, 4096);
$annotations->parse_string($text);

//$annotations->set_uuid(uniqid());
//$annotations->set_created(date('Y-m-d H:j:s (P)'));
//$annotations->set_modified(date('Y-m-d H:j:s (P)'));
//$annotations->set_created_by('ptomulik');
//$annotations->set_modified_by('ptomulik');

include('/home/ptomulik/projects/wha/installdir/share/WHA/apache.conf.php')


// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
