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


require_once('WHA/CliCmds.php');    // register all CLI commands
require_once('WHA/CliCmdFactory.php');

/**
 * Entry point to CLI version of wha.
 *
 * @param int
 * @param array
 *
 * @return int Exit code.
 * @since 0.1
 */
function wha_cli_main($argc, $argv) {
    $factory = WHA_CliCmdFactory::instance();

    $cmd = $factory->createCmdFromArgv($argv, $err);
    if($cmd === FALSE) {
        // happens if there is syntax error on command-line
        $script =  WHA_Cli::helpScriptName($argv[0]);
        fwrite(STDERR, "error: $err\n");
        fwrite(STDERR, "try: '" . $script. " help' to get some help\n");
        return 1;
    }

    // TODO: catch exceptions here?
    $cmd->execute();

    $ecode = $cmd->getCliExitCode();
    if($ecode != 0) {
        $emsg =  $cmd->getCliErrorMessage();
        if (!isset($emsg)) $emsg = '(unknown error)';
        fwrite(STDERR, 'error: ' . $emsg . "\n");
    }
    return $ecode;
}

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
