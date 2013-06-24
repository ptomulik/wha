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


require_once('WHA/CliCmd.php');
require_once('WHA/CliCmdFactory.php');
require_once('Config.php');

class WHA_CliCmdTest extends WHA_CliCmd
{
    // $_cmd_info {{{
    private static $_cmd_info = array(
        'purp' => 'temporary command for development and tests'
    );
    // }}}
    // $_cmd_opts {{{
    private static $_cmd_opts = array(
        '--path' => array(
            'name' => 'path',
            'type' => 'path',
            'help' => 'this should be any path'
        ),
        '--file' => array(
            'name' => 'file',
            'type' => 'path',
            'help' => 'this should be a file'
        ),
    );
    // }}}
    // $_cmd_args {{{
    private static $_cmd_args = array(
        'range' => array(1,1),
        0 => array('file' => 'file', 'help' => 'input file')
    );
    // }}}
    // registerThisCmd() {{{
    public static function registerThisCmd() {
        $cf = WHA_CliCmdFactory::instance();
        if(!$cf->hasRegistered('test'))
            $cf->registerCmd('WHA_CliCmdTest', 'test', self::$_cmd_info,
                             self::$_cmd_opts, self::$_cmd_args);
    }
    // }}}
    // execute () {{{
    public function execute()
    {
        $config = new Config();
        $root = $config->parseConfig($this->getCliArgument(0), 'Apache');
        var_dump($root);
    }
    // }}}
};

 WHA_CliCmdTest::registerThisCmd();

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
