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


require_once('WHA/file_annotations.php');

class WHA_ShellConfigFileAnnotations extends WHA_FileAnnotations
{
    public function get_open_tag()
    { return '--WHA_ShellConfigFileAnnotations'}
    public function get_close_tag()
    { return '--WHA_ShellConfigFileAnnotations--'; }
    public function get_filetype()
    { return 'WHA_SHELL_CONF'; }

    public function parse_string($string)
    {
        $open_tag_met=FALSE;
        $close_tag_met=FALSE;

        // We process comments only
        $lines = preg_grep('/^\s*#/', explode("\n", $string));
        foreach ($lines as $line) {
            // Strip-out comments and leading/trailing spaces
            $line = preg_replace('/^\s*#+\s*(.*)$/', '${1}', $line);
            $line = preg_replace('/^(.*)\s+$/', '${1}', $line);
            if (!$open_tag_met) {
                if(strcmp($line, $this->get_open_tag()) == 0)
                    $open_tag_met = TRUE;
            } elseif (!$close_tag_met) {
                if(strcmp($line, $this->get_close_tag()) == 0)
                    $close_tag_met = TRUE;
            }

            if($open_tag_met && !$close_tag_met) {
                $res = preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*:\s*(.+)$/', $line,
                    $matches);
                if ($res === FALSE) {
                    trigger_error("WHA_ShellConfigFileAnnotations: parse_string:".
                        " preg_match() failed.");
                } elseif ($res) {
                    $name = $matches[1];
                    $value = $matches[2];
                    if (in_array($name, $this->mutable_names()))
                        $this->set_annotation($name, $value);
                }
            }
        }
    }
}


// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
