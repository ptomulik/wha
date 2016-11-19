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
 * command: help
 * --------------------------------------------------------------------------*/
$wha_commands['help'] = array();
$wha_commands['help']['help'] = "Print help message to stdout and exit.";
$wha_commands['help']['handler'] = 'wha_command_help';
$wha_arguments['help'] = array();
$wha_arguments['help']['range'] = array(0,1);
$wha_arguments['help'][0]['name'] = 'command';
$wha_arguments['help'][0]['help'] = 'display help for this command';

function wha_get_help_string($script, $args = array())
{

    global $wha_commands;
    global $wha_options;
    global $wha_arguments;

    $hscript = $script;

    $syspath = getenv('PATH');
    if (is_string($syspath)) {
        $scriptdir = dirname(realpath($script));
        $paths = explode(PATH_SEPARATOR, $syspath);
        foreach ($paths as $p) {
            $pathdir = realpath($p);
            if(strcmp($pathdir, $scriptdir) == 0) {
                $hscript = basename($script);
                break;
            }
        }
    }

    if(count($args) == 0) {
        $help  = "USAGE:\n";
        $help .= "    " .$hscript . " [common options] <command> [options] [args]";
        $help .= "\n\nCOMMON OPTIONS:";
        foreach($wha_options['common'] as $key => $info) {
            $help .= sprintf("\n    %-16.16s%s", $key, $info['help']);
        }
        $help .= "\n\nCOMMANDS:";
        foreach($wha_commands as $key => $info) {
            $help .= sprintf("\n    %-16.16s%s", $key, $info['help']);
        }
        $help .= "\n\nSee '". $hscript ." help <command>' for more information";
    } else {
        $cmd = $args[0];
        if(!array_key_exists($cmd, $wha_commands)) {
            fwrite(STDOUT, "help: unknown command '". $cmd . "'\n");
            return FALSE;
        }

        $have_common_options = !in_array($cmd, array('help', 'version'));
        $have_options =  array_key_exists($cmd, $wha_options)
            && is_array($wha_options[$cmd])
            && count($wha_options[$cmd]) > 0;

        $help  = "USAGE:\n";
        $help .= "    " .$hscript ;
        if($have_common_options) $help .= " [common options]";
        $help .= " $cmd";
        if ($have_options) $help .= " [options]";

        list($args_min, $args_max)
            = wha_parse_args_get_args_range($cmd, $wha_arguments);

        for ($argi = 0; $argi < $args_max; $argi++) {
            if($argi > 5) {
                $help .= ' ...';
                break;
            }
            if(array_key_exists($argi, $wha_arguments[$cmd]) &&
                array_key_exists('name', $wha_arguments[$cmd][$argi])) {
                    $help .= " ";
                    if($argi >= $args_min) $help .= '[';
                    $help .= $wha_arguments[$cmd][$argi]['name'];
                    if($argi >= $args_min) $help .= ']';
                } else {
                    $help .= ' ...';
                    break;
                }
        }

        $help .= "\n\nDESCRIPTION:";
        $help .= sprintf("\n    %s", $wha_commands[$cmd]['help']);

        if($have_common_options) {
            $help .= "\n\nCOMMON OPTIONS:";
            foreach($wha_options['common'] as $key => $info) {
                $help .= sprintf("\n    %-16.16s%s", $key, $info['help']);
            }
        }

        if($have_options){
            $help .= "\n\nOPTIONS:";
            $opts = $wha_options[$cmd];
            foreach($opts as $key => $info) {
                $help .= sprintf("\n    %-16.16s%s", $key, $info['help']);
            }
        }
        $args_hdr_printed = FALSE;
        for ($argi = 0; $argi < $args_max; $argi++) {
            if(array_key_exists($argi, $wha_arguments[$cmd]) &&
                array_key_exists('name', $wha_arguments[$cmd][$argi]) &&
                array_key_exists('help', $wha_arguments[$cmd][$argi])) {
                    if (!$args_hdr_printed) {
                        $help .= "\n\nARGUMENTS:";
                        $args_hdr_printed = TRUE;
                    }
                    $key =  $wha_arguments[$cmd][$argi]['name'];
                    $info =  $wha_arguments[$cmd][$argi]['help'];
                    $help .= sprintf("\n    %-16.16s%s", $key, $info);
                } else {
                    break;
                }
        }
    }
    return $help;
}

/* --------------------------------------------------------------------------
 * wha_command_help()
 *
 * Display help and return.
 * --------------------------------------------------------------------------*/
function wha_command_help($script, $command, $common_opts, $opts, $args)
{
    $help =  wha_get_help_string($script, $args);
    if ($help !== FALSE) {
        fwrite(STDOUT, $help."\n");
        return 0;
    } else {
        return 1;
    }
}

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
