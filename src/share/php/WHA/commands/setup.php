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


/* --------------------------------------------------------------------------
 * command: setup
 * --------------------------------------------------------------------------*/
$wha_commands['setup'] = array();
$wha_commands['setup']['help'] = "Change WHA settings.";
$wha_commands['setup']['handler'] = "wha_command_setup";
// options
$wha_options['setup'] = array();
// -i
$wha_options['setup']['-i'] = array();
$wha_options['setup']['-i']['help'] = "create initial configuration";
// -f 
$wha_options['setup']['-f']= array();
$wha_options['setup']['-f']['help'] = "force overriding configuration files";
// arguments
//$wha_arguments['setup'] = array();


require_once('WHA/commands/setup/dialog.php');

function wha_command_setup($script, $command, $common_opts, $opts, $args)
{
  if(array_key_exists('-i', $opts) && $opts['-i']) {
  } else {
    return wha_command_setup_dialog($script,$command,$common_opts,$opts,$args);
  }
}
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
