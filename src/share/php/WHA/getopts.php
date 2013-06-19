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


function wha_parse_args_get_args_range($cmd, $arguments)
{
    $args_min = 0;
    $args_max = 0;

    if(is_array($arguments) && array_key_exists($cmd, $arguments)) {
        $arguments = $arguments[$cmd];
        if (is_array($arguments)) {
            if(array_key_exists('range', $arguments)) {
                $rng = $arguments['range'];
                $args_min = array_key_exists(0, $rng) ?  $rng[0] : 0;
                $args_max = array_key_exists(1, $rng) ?  $rng[1] : 65535;
            } 
        } elseif ($arguments) {
            $args_min = 0;
            $args_max = 65536;
        }
    }

    return array($args_min, $args_max);
}

function wha_parse_args($argc, $argv, &$err, $commands, $opts, $args=array())
{
    $x_script = null;
    $x_command = null;
    $x_opts = array( 'common' => array ( ) );
    $x_args = array( 'common' => array ( ) );
    $x_sect = 'common';

    // for options with values
    $x_optval_opt = null;   // option name, for which we expect the value
    $x_optval_type = null;  // type of the expected value
    $x_optval_req = null;   // is the value required?

    $x_args_min = 0;
    $x_args_max = 0;

    foreach($argv as $arg) {
        if($x_script === null) {
            $x_script = $arg;
        } else {
            if(array_key_exists($x_sect, $opts) &&
                array_key_exists($arg, $opts[$x_sect])) {

                    if ($x_optval_opt !== null && $x_optval_req === TRUE) {
                        $err = "option '" . $x_optval_opt . "' requires a value";
                        return FALSE;
                    } 

                    if (array_key_exists($arg, $x_opts[$x_sect])) {
                        $err = "repeated option ".$arg." (in ". $x_sect . ")\n";
                        return FALSE;
                    }

                    // shall we expect option value in next iteration?
                    if (is_array($opts[$x_sect][$arg])) {
                        if(array_key_exists ('type', $opts[$x_sect][$arg])) {
                            $x_optval_type = $opts[$x_sect][$arg]['type'];
                            $x_optval_opt = $arg;
                            if(array_key_exists ('required', $opts[$x_sect][$arg])) {
                                $x_optval_req = $opts[$x_sect][$arg]['required'] ? TRUE : FALSE;
                            } else {
                                $x_optval_req = TRUE;
                            }
                            $x_opts[$x_sect][$arg] = null;
                        } else {
                            $x_optval_type = null;
                            $x_optval_req = null;
                            $x_optval_opt = null;
                            $x_opts[$x_sect][$arg] = TRUE;
                        }
                    } else {
                        $x_opts[$x_sect][$arg] = TRUE;
                    }

                } else {

                    if ($x_optval_opt !==null) {

                        // FIXME: validate option argument
                        $x_opts[$x_sect][$x_optval_opt] = $arg;
                        $x_optval_type = null;
                        $x_optval_req = null;
                        $x_optval_opt = null;

                    } elseif(!$x_command) {

                        if(in_array($arg, $commands)) {

                            $x_command = $arg;
                            $x_opts[$arg] = array( );
                            $x_args[$arg] = array( );
                            $x_sect = $arg;

                            list($x_args_min, $x_args_max) 
                                = wha_parse_args_get_args_range($x_command, $args);

                        } else {
                            $err = 'unsupported ' . 
                                ((strlen($arg)>0 && $arg[0] == '-') ?  'option' : 'command') .
                                " '". $arg . "'";
                            return FALSE;
                        }

                    } else {
                        // positional arguments to command?
                        if(count($x_args[$x_sect]) >= $x_args_max) {
                            $err = "too many arguments to command '" . $x_command . "'";
                            return FALSE;
                        } else {
                            array_push($x_args[$x_sect], $arg);
                        }
                    }
                }
        }
    }

    if ($x_command === null) {
        $err = 'missing command';
        return FALSE;
    }

    // We may still have missing option value
    if ($x_optval_opt !== null && $x_optval_req === TRUE) {
        $err = "option '" . $x_optval_opt . "' requires a value";
        return FALSE;
    } 

    // Do we have all required arguments for our command
    if(count($x_args[$x_command]) < $x_args_min) {
        $err = "too feew arguments to command '" . $x_command . "'";
        return FALSE;
    }

    return array($x_script, $x_command, $x_opts, $x_args);
}

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
