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

require_once('WHA/getopts.php');
require_once('WHA/tools.php');

/* --------------------------------------------------------------------------
 * supprted commands, options and positional arguments
 * --------------------------------------------------------------------------*/
$wha_commands = array();
$wha_options = array();
$wha_arguments = array();

require_once('WHA/commands.php');

/* --------------------------------------------------------------------------
 * common options 
 * --------------------------------------------------------------------------*/
$wha_options['common'] = array();
// -q
$wha_options['common']['-q'] = array();
$wha_options['common']['-q']['help'] = "quiet mode";
// -v
$wha_options['common']['-v'] = array();
$wha_options['common']['-v']['help'] = "verbose mode";
// -y
$wha_options['common']['-y'] = array();
$wha_options['common']['-y']['help'] = "answer yes to all questions";
// -n
$wha_options['common']['-n'] = array();
$wha_options['common']['-n']['help'] = "answer no to all questions";


/* --------------------------------------------------------------------------
 * wha_main() 
 *
 *  Main function starting the whole machinery.
 * --------------------------------------------------------------------------*/
function wha_main($argc, $argv)
{

    global $wha_commands;
    global $wha_options;
    global $wha_arguments;

    $args = wha_parse_args( $argc, $argv, $err, array_keys($wha_commands),
        $wha_options, $wha_arguments );

    if($args === FALSE) {
        fwrite (STDERR, wha_get_help_string($argv[0])."\n");
        fwrite (STDERR, "error: ". $err . "\n");
        return 1;
    }

    list($script, $command, $options, $arguments) = $args;

    if(!array_key_exists($command, $wha_commands)) {
        fwrite (STDERR, "error: wha_main(): internal error, wrong command '" .
            $command . "' returned by wha_arg_parse()'\n");
        return 1;
    }
    if(!array_key_exists('handler', $wha_commands[$command])) {
        fwrite (STDERR, "error: wha_main(): internal error, handler for '" .
            $command . "' is not defined\n");
        return 1;
    }
    $handler  = $wha_commands[$command]['handler'];
    if(!is_callable($handler)) {
        fwrite (STDERR, 'error: wha_main(): internal error, command handler is '.
            'not callable\n');
        return 1;
    } 
    $common_opts = $options['common'];
    $opts = $options[$command];
    $args = $arguments[$command];

    if(!wha_tools_init($err)) {
        fwrite(STDERR, "error: failed to initialize tools: ". $err ."\n");
        return 1;
    }
    return call_user_func($handler,$script,$command,$common_opts,$opts,$args);
}

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
