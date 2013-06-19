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


/**
 * Encapsulates definition of CLI commands, options, and positional arguments
 * for the script.
 *
 * This object maintains information about command-line commands, options and 
 * positional arguments supported by **wha**. It also acts as command-line 
 * parser and may be used to generate help message. 
 * 
 * Example:
 * <code>
 * require_once('WHA/Cli.php');
 * $cli = & new WHA_Cli();
 * $cli->cmds['moo'] = array( 'purp' => 'say moo' );
 * ...
 * </code>
 *
 * The command-line implemented by {@link WHA_Cli} has the following form
 *
 * <code>
 *      wha [common options] <command> [options] [args]
 * </code>
 *
 * The class maintains associative array {@link $cops} to describe supported 
 * `common options` and three associative arrays: {@link $cmds}, {@link $opts}, 
 * and {@link $args} to describe supported commands, their `options`, and 
 * positional arguments `args`.
 *
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
class WHA_Cli 
{
    // $cops {{{
    /** 
     *  Definition of common command-line options.
     *
     *  This is an associative array, whose keys correspond to common options 
     *  supported by **wha**. For example, an array
     *  <code>
     *      array(
     *          '-d' => array( 'help' => 'debug mode' ),
     *          '-q' => array( 'help' => 'quiet mode' ),
     *          '-v' => array( 'help' => 'verbose mode' )
     *      )
     *  </code>
     *  with keys `'-d'`, `'-q'`, `'-v'` corresponds to the following 
     *  command-line syntax
     *  <code>
     *  wha [-d] [-q] [-v] foo [options] ...
     *  </code>
     *
     *  The format of each item is same as for commands' options. See 
     *  documentation of {@link $opts} member variable.
     *
     *  @var array
     *  @since 0.1
     */ 
    public $cops;
    // }}}
    // $cmds {{{
    /** 
     *  Definition of command-line commands.
     *
     *  This is an associative array, whose keys correspond to commands 
     *  supported by the script. For example, `$cmds['foo']` corresponds 
     *  to the command
     *  <code>
     *       wha foo
     *  </code>
     *
     *  Each item describes one command, and should have form:
     *  <code>
     *       $cli->cmds['foo'] = array(
     *           'purp'  => 'purpose - one line info'
     *       );
     *  </code>
     *
     *  @var array $cmds
     *  @since 0.1
     */
    public $cmds;
    //}}}
    // $opts {{{
    /** 
     *  Definition of command-line options.
     *
     *  This is an associative array whose keys correspond to the keys of 
     *  {@link $cmds}, that is to the commands supported by the script. For 
     *  example `$opts['foo']` corresponds to the command
     *  <code>
     *  wha [common options] foo [options] ...
     *  </code>
     *  and describes the `options`. 
     *
     *  The format of each item is the following (items in `[]` are 
     *  optional)
     *  <code>
     *  $cli->opts['foo'] = array(
     *      '--xxx' => array(
     *          ['type' => 'string',]
     *          ['required' => true or false,]
     *          'help' => 'one line help for option'
     *      ),
     *      '--yyy' => array( ... )
     *  )
     *  </code>
     *
     *  The item with key `'--xxx'` describes option `--xxx` for 
     *  command `foo`. 
     *
     *  The option is described by an associative array with the following keys 
     *  <ul>
     *      <li>`type`       - if the option accepts a value
     *                                (i.e `--xxx val`),</li>
     *      <li>`required`   - to tell whether the value is required 
     *                                or not (by default it is required),</li>
     *      <li>`help`       - one line help message,</li>
     *  </ul>
     *
     *  If the key `'type'` is present, the option parser expects value 
     *  for the option `--xxx`. By default the value is required - this 
     *  can be changed by setting `'required'` to `false`.
     *
     *  @var array
     *  @since 0.1
     */ 
    public $opts;
    // }}}
    // $args {{{
    /**
     * Definition of command-line positional arguments.
     *
     * This is an associative array, whose keys correspond to keys from 
     * {@link $cmds}, that is to the commands supported by the script. For 
     * example, `$args['foo']` corresponds to the command
     * <code>
     *      wha foo [xxx [yyy ... ] ] 
     * </code>
     *
     * The item `$args['foo']` describes positional arguments supported
     * by command 'foo'. To describe positional arguments we need to provide 
     * the number of arguments accepted by command and eventually its name with 
     * help messages. An example definition of `$args['foo']` for command 'foo' 
     * (above) is the following
     * <code>
     *  $cli->args['foo'] = array(
     *      'range' => array(0,null),
     *      0   =>  array(
     *          'name'  =>  'xxx'
     *          'help'  =>  'one line help for xxx',
     *      ),
     *      1   =>  array(
     *          'name'  =>  'yyy'
     *          'help'  =>  'one line help for yyy',
     *      )
     *  )
     * </code>
     *
     * In this case `$args['foo']` says that command `foo` takes zero or more 
     * positional arguments (the `'range'` item), the first argument is 
     * named `xxx`, the second and remaining arguments are reffered as `yyy`'s.
     *
     * For parser to recognize that command may accept arguments, it is enough 
     * to provide just the `'range'` item. 
     *
     * To generate meaningful help messages, however, it is necessary to 
     * provide also `'name'`s with `'help'` strings for first `n` arguments.
     *
     * The possible items in `$args['foo']` are:
     * <ul>
     *  <li>`range` - range of arguments supported by command. It may 
     *  be: 
     *  <ul>
     *      <li>`array($min,$max)` - minimum and maximum number of arguments to 
     *      command `'foo'`,</li>
     *      <li>`array($min)` or `array($min,null)` - command accepts `$min` or 
     *      more arguments,</li>
     *      <li>`array(null, $max)` or `array(1 => $max)` - command accepts 
     *      zero to `$max` arguments,</li>
     *      <li>`array(0)`, `array(0,null)` or `true` - command accepts zero or 
     *      more arguments (max unbounded).</li>
     *  </ul>
     *  By default (`$args['foo']['range']` not defined or isn't any of the 
     *  above) it is assumed that command supports no arguments ($min=0, 
     *  $max=0). 
     *
     *  Use {@link argsRangeFor()} to query the concrete range of supported 
     *  arguments for a command. 
     *  </li>
     *  <li>`0, .., n` - additional information about first `n` 
     *  positional arguments.</li>
     * </ul>
     *
     * The possible items in  `$args['foo'][$i]` (`$i = 0, .., n` - integer) 
     * are <ul>
     *  <li>`name` name of the argument (required)</li>
     *  <li>`help` help string for the argument (required)</li>
     * </ul>
     *
     * If you don't mean do describe all the arguments, you may define only 
     * first n items in `$args['foo']`.
     *
     * @var array
     * @since 0.1
     */
    public $args;
    // }}}
    // $_cops_default {{{
    /**
     * Default common options {@link $cops}, supported by all commands.
     *
     * @var array
     */
    private static $_cops_default = array(
        '-d' => array( 'help' => 'debug mode' ),
        '-q' => array( 'help' => 'quiet mode' ),
        '-v' => array( 'help' => 'verbose mode' )
    );
    // }}}
    // __construct([$cmds,$opts,$args,$cops]) {{{
    /**
     *  Constructor for {@link WHA_Cli}
     *
     *  @param array Used to initialize `$this->cmds`
     *  @param array Used to initialize `$this->opts`
     *  @param array Used to initialize `$this->args`
     *  @param array Used to initialize `$this->opts['common']`
     *  @since 0.1
     */
    public function __construct($cmds = null, $opts = null, $args = null, 
                                $cops = null)
    {
        if($cmds === null) {
            $this->cmds = array();
        } else {
            $this->cmds = $cmds;
        }
        if($opts === null) {
            $this->opts = array();
        } else {
            $this->opts = $opts;
        }
        if($args === null) {
            $this->args = array();
        } else {
            $this->args = $args;
        }
        if($cops === null) {
            $this->cops = self::$_cops_default;
        } else {
            $this->cops = $cops;
        }
    }
    // }}}
    // parseArgv($argv,&$err) {{{
    /**
     * Parse command line arguments. 
     *
     * Example usage:
     * <code>
     *  $cli = WHA_Cli::instance();
     *  $x = $cli->parseArgv($argv, $err);
     *  if ($x === false) {
     *      fwrite(STDERR, "error: $err");
     *      exit(1);
     *  }
     *  list($script, $cops, $cmd, $opts, $args) = $x;
     *  // $script - (string) this script name (equals $argv[0]),
     *  // $cops - (array) common options, passed before command
     *  // $cmd - (string) command name,
     *  // $opts - (array) options passed after the command,
     *  // $args - (array) positional arguments
     * </code>
     *
     * After successful parsing, `$script` is always set to `$argv[0]`, 
     * `$cops` and `$opts` are associative arrays and have only items 
     * for options found in `$argv` (so you may use `array_keys($opts)` 
     * to list names of all the options extracted from `$argv`), `$cmd` is
     * a string with command name found in `$argv`, and `$args` is a plain 
     * array of values for positional arguments found in `$argv`.
     *
     * @param array Array of command line arguments passed to script (namely,
     *              its the PHP's <a href="http://php.net/manual/en/reserved.variables.argv.php">$argv</a>).
     * @param string Error message
     *
     * @return array|false  On success returns 5-element array, on error 
     *                      returns false
     */
    public function parseArgv($argv, &$err = null)
    {
        $err = null;

        // parsing state:
        $s = array( 'scr' => $argv[0],  // script name
                    'cops' => array(),  // common options
                    'cmd' => null,      // command name
                    'opts' => array(),  // options (for the selected command)
                    'args' => array() );// positional arguments

        $argsmin = 0;
        $argsmax = 0;
        for($i = 1; $i < count($argv); $i++) {
            $arg = $argv[$i];
            if(!isset($s['cmd'])) {
                // command not set, look for common options then for command
                if($this->_try_optval($arg, $s, $err)) continue;
                if(isset($err)) return false;
                if($this->_try_option($arg, $s, $err)) continue;
                if(isset($err)) return false;
                if($this->_try_command($arg, $s, $err)) {
                    list($argsmin, $argsmax) = $this->argsRangeFor($s['cmd']);
                    continue;
                }
                if(!isset($err)) {
                    $oc = (strlen($arg)>0 && $arg{0} == '-') 
                        ?  'option' : 'command';
                    $err = 'unsupported ' . $oc .  " '". $arg . "'";
                }
                return false;
            } elseif(!isset($s['args'][0])) {
                // positional arguments were not seen, so we still may expect
                // options (possibly with values) here
                if($this->_try_optval($arg, $s, $err)) continue;
                if(isset($err)) return false;
                if($this->_try_option($arg, $s, $err)) continue;
                if(isset($err)) return false;
            }
            // It's an argument, let's see if we can accept it
            if(count($s['args']) >= $argsmax) {
                $err = 'too many positional arguments';
                return false;
            }
            $s['args'][] = $arg;
        }

        if(isset($s['optv']) && $s['optv']['req']) {
            $err = 'missing value for option ' . $s['optv']['opt'];
            return false;
        }
        if(!isset($s['cmd'])) {
            $err = 'missing command';
            return false;
        }
        if(count($s['args']) < $argsmin) {
            $err = 'too feew positional arguments';
            return false;
        }
        return array($s['scr'],$s['cops'],$s['cmd'], $s['opts'],$s['args']);
    }
    // }}}
    // helpString($script, $cmd = null) {{{
    /**
     *  Return help string for a command.
     *
     *  @param string name of the script
     *  @param string name of the command for which to generate help
     *
     *  @return string
     *  @since 0.1
     */
    public function helpString($script, $cmd = null)
    {
        $script = self::helpScriptName($script);

        if(!isset($cmd)) {
            $help  = "USAGE:\n";
            $help .= "    " .$script . " [common options] <command> [options] [args]";
            $help .= "\n\nCOMMON OPTIONS:";
            foreach($this->cops as $key => $info) {
                $help .= sprintf("\n    %-16.16s%s", $key, $info['help']);
            }
            $help .= "\n\nCOMMANDS:";
            $keys = array_keys($this->cmds);
            sort($keys);
            foreach($keys as $key) {
                $info = $this->cmds[$key];
                $help .= sprintf("\n    %-16.16s%s", $key, $info['purp']);
            }
            $help .= "\n\nSee '". $script ." help <command>' for more information";
        } else {
            if(!isset($this->cmds[$cmd])) {
                fwrite(STDOUT, "help: unknown command '". $cmd . "'\n");
                return false;
            }

            $have_common_options = !in_array($cmd, array('help', 'version'));
            $have_options =  isset($this->opts[$cmd])
                && is_array($this->opts[$cmd])
                && count($this->opts[$cmd]) > 0;

            $help  = "USAGE:\n";
            $help .= "    " .$script ;
            if($have_common_options) $help .= " [common options]";
            $help .= " $cmd";
            if ($have_options) $help .= " [options]";

            list($args_min, $args_max) = $this->argsRangeFor($cmd);

            for ($argi = 0; $argi < $args_max; $argi++) {
                if($argi > 5) {
                    $help .= ' ...';
                    break;
                }
                if(isset($this->args[$cmd][$argi]) &&
                    isset($this->args[$cmd][$argi]['name'])) {
                        $help .= " ";
                        if($argi >= $args_min) $help .= '[';
                        $help .= $this->args[$cmd][$argi]['name'];
                        if($argi >= $args_min) $help .= ']';
                    } else {
                        $help .= ' ...';
                        break;
                    }
            }

            $help .= "\n\nPURPOSE:";
            $help .= sprintf("\n    %s", $this->cmds[$cmd]['purp']);

            if($have_common_options) {
                $help .= "\n\nCOMMON OPTIONS:";
                foreach($this->cops as $key => $info) {
                    if(isset($info['name']) && is_string($info['name']))
                        $help .= sprintf("\n    %-16.16s%s", $key.' '.$info['name'], $info['help']);
                    else
                        $help .= sprintf("\n    %-16.16s%s", $key, $info['help']);
                }
            }

            if($have_options){
                $help .= "\n\nOPTIONS:";
                $opts = $this->opts[$cmd];
                foreach($opts as $key => $info) {
                    if(isset($info['name']) && is_string($info['name']))
                        $help .= sprintf("\n    %-16.16s%s", $key.' '.$info['name'], $info['help']);
                    else
                        $help .= sprintf("\n    %-16.16s%s", $key, $info['help']);
                }
            }
            $args_hdr_printed = false;
            for ($argi = 0; $argi < $args_max; $argi++) {
                if(isset($this->args[$cmd][$argi]['name']) &&
                   isset($this->args[$cmd][$argi]['help'])) {
                        if (!$args_hdr_printed) {
                            $help .= "\n\nARGUMENTS:";
                            $args_hdr_printed = true;
                        }
                        $key =  $this->args[$cmd][$argi]['name'];
                        $info =  $this->args[$cmd][$argi]['help'];
                        $help .= sprintf("\n    %-16.16s%s", $key, $info);
                    } else {
                        break;
                    }
            }
            if(isset($this->cmds[$cmd]['help']) && 
                is_string($this->cmds[$cmd]['help']) && 
                strlen($this->cmds[$cmd]['help']) > 0) {
                    $help .= "\n\nDESCRIPTION:";
                    $help .= "\n".$this->cmds[$cmd]['help'];
            }
        }
    return $help;
    }
    // }}}
    // helpScriptName() {{{
    /**
     * Convert `$argv[0]` to user-friendly script name, strippig the leading 
     * path in `$argv[0]` if it is in system's `$PATH`.
     *
     * @param string script name as obtained from `$argv[0]`
     * @return string user-friendly script name
     * @since 0.1
     */
    public static function helpScriptName($argv0) {
        $hscript = $argv0;

        // if script is in path, show only its basename
        $syspath = getenv('PATH');
        if (is_string($syspath)) {
            $scriptdir = dirname(realpath($argv0));
            $paths = explode(PATH_SEPARATOR, $syspath);
            foreach ($paths as $p) {
                $pathdir = realpath($p);
                if(strcmp($pathdir, $scriptdir) == 0) {
                    $hscript = basename($argv0);
                    break;
                }
            }
        }
        return $hscript;
    }
    // }}}
    // argsRangeFor($cmd) {{{
    /**
     *  Tell how many arguments are required/supported by command $cmd
     *
     *  This method returns array($min,$max) with $min being minimum number of 
     *  required positional arguments and $max being the maximum number of 
     *  arguments supported by command $cmd. If maximum is not defined, `$max` 
     *  equals to <a 
     *  href="http://php.net/manual/en/reserved.constants.php">`PHP_INT_MAX`</a>.
     *
     *  @param string Command name
     *
     *  @return array Always 2-element array `array($min,$max)`.
     *
     *  @since 0.1
     */
    public function argsRangeFor($cmd)
    {
        $min = 0;
        $max = 0;

        if(is_array($this->args) && array_key_exists($cmd, $this->args)) {
            $args = $this->args[$cmd];
            if(is_array($args)) {
                if(array_key_exists('range', $args)) {
                    $rng = $args['range'];
                    $min = array_key_exists(0, $rng) ?  $rng[0] : 0;
                    $max = array_key_exists(1, $rng) ?  $rng[1] : PHP_INT_MAX;
                    if($min === null) $min = 0;
                    if($max === null) $max = PHP_INT_MAX;
                } 
            } elseif($args) {
                $min = 0;
                $max = PHP_INT_MAX;
            }
        }
        return array($min, $max);
    }
    // }}}
    // _try_command($arg, &$s, &$err) {{{
    /**
     * Try to interpret current argument from `$argv` list as command name.
     *
     * Returns `true` if command found, or `false` otherwise. In case of error,
     * `false` is returned and `$err` constains error message. If `$arg`
     * is not a command but no error was detected `$err` remains unchanged.
     *
     * On exit sets:
     * <ul>
     *  <li>`$s['cmd'] = $arg` - if $arg is a supported command</li>
     *  <li>`$err` - if error occurred</li>
     * </ul>
     *
     * @param string Current argument from `$argv` list
     * @param array State array (may be updated inside the function)
     * @param string Error message
     *
     * @return bool true if option found or false otherwise
     * @since 0.1
     */
    protected function _try_command($arg, &$s, &$err) {
        if (!isset($this->cmds[$arg])) return false;
        $s['cmd'] = $arg;
        return true;
    }
    // }}}
    // _try_option($arg, &$s, &$err) {{{
    /**
     * Try to interpret current argument from `$argv` list as an option.
     *
     * Returns `true` if option found, or `false` otherwise. In case of error,
     * `false` is returned and `$err` constains error message. If option 
     * is not found but no error was detected `$err` remains unchanged.
     *
     * On exit sets:
     * <ul>
     *  <li>`$s['cops']['--foo'] = true` - if common option `--foo` was 
     *  found</li>
     *  <li>`$s['opts']['--foo'] = true` - if command's option `--foo` was 
     *  found</li>
     *  <li>`$s['optv'] = array(...)` - if an option was found and it accepts
     *  a value</li>
     *  <li>`$err` - if error occurred</li>
     * </ul>
     *
     * @param string Current argument from `$argv` list
     * @param array State array (may be updated inside the function)
     * @param string Error message
     *
     * @return bool true if option found or false otherwise
     * @since 0.1
     */
    protected function _try_option($arg, &$s, &$err) {

        if(!isset($s['opts'])) $s['opts'] = array();
        if(!isset($s['cops'])) $s['cops'] = array();

        if(!isset($s['cmd'])) {
            // Process common options
            $sect = 'common';
            $opts = $this->cops;
            $okey = 'cops';
        } else {
            // Process command options
            $sect = $s['cmd'];
            if(!isset($this->opts[$sect])) {
                // No options defined for this command
                return false;
            }
            $opts = $this->opts[$sect];
            $okey = 'opts';
        }

        if(!isset($opts[$arg])) {
            return false;
        }

        // do we expect option value?
        if(isset($s['optv']) && $s['optv']['req']) {
            $o = $s['optv']['opt'];
            $err = "option '" . $o . "' requires a value";
            return false;
        }
        // have we already seen the option?
        if(isset($s[$okey][$arg])) {
            // TODO: implement repeated options?
            $err = "repeated option " . $arg . " (in ". $sect . ")";
            return false;
        }

        // shall we expect option value in next turn?
        if(is_array($opts[$arg])) {
            if(isset($opts[$arg]['type'])) {
                $optv = array(
                    'type' => $opts[$arg]['type'],
                    'opt'  => $arg,
                    'key'  => $okey
                );
                if(isset($opts[$arg]['required'])) {
                    $optv['req'] = $opts[$arg]['required'] ? true : false;
                } else {
                    $optv['req'] = true;
                }
                $s['optv'] = $optv;
            } 
        }
        $s[$okey][$arg] = true;

        return true;
    }
    // }}}
    // _try_optval($arg, &$s, &$err) {{{
    /**
     * Eat current argument from `$argv` list if we expect option value.
     *
     * On exit sets:
     * <ul>
     *  <li>`$s['cops']['--foo'] = $arg` - if value for common option `--foo` 
     *  was processed</li>
     *  <li>`$s['opts']['--foo'] = $arg` - if value for command option `--foo` 
     *  was processed</li>
     *  <li>`$s['optv'] = null` - if option value was processed</li>
     *  <li>`$err` - if error occurred</li>
     * </ul>
     *
     * @param string Current argument from `$argv` list
     * @param array State array (may be updated inside the function)
     * @param string Error message
     *
     * @return bool true if we processed the value or false otherwise
     * @since 0.1
     */
    protected function _try_optval($arg, &$s, &$err) {

        if(!isset($s['optv'])) return false;

        $opt = $s['optv']['opt'];
        $key = $s['optv']['key'];
        if(!$this->isValid($arg, $s['optv']['type'], $err)) {
            $err = "$opt - $err";
            return false;
        }
        $s[$key][$opt] = $arg;
        $s['optv'] = null;

        return true;
    }
    // }}}
    // isValid($value, $type) {{{
    /**
     * Check if the `$value` if valid for given `$type`. Supported types:
     * <ul>
     * <li>`string` - actually no checking</li>
     * <li>`file` - check if the string contains well-formed file name</li>
     * <li>`path` - check if the string contains well-formed path (file or 
     *              directory)</li>
     * </ul>
     *
     * @param string a value to be validated
     * @param string type of the `$value`
     * @param string to return error message 
     * @return bool `true` on success, `false` if `$value` is not valid, 
     *              or if `$type` is not supported.
     * @since 0.1
     */
    public static function isValid($value, $type, &$err = null) {
        $ds = DIRECTORY_SEPARATOR;
        switch($type) {
            case 'string':
                return is_string($value);
            case 'file':
                $re = "#(?:(?:$ds(?:\\.{0,2})?)|(?:^\\.{1,2}))\$#";
                if(preg_match($re, $value)) {
                    $err = "directory name '$value' not allowed here";
                    return false;
                }
                if(!preg_match('#[[:alnum:]]#', basename($value))) {
                    // we wish at least one alphanumeric in basename 
                    $err = "malformed file name '$value'";
                    return false;
                }
            case 'path':
                if(trim($value) == '') {
                    $err = "empty string not allowed here";
                    return false;
                }
                if(preg_match('#\\s#', $value)) {
                    $err = "whitespaces are not allowed ('$value')";
                    return false;
                }
                $parts = explode($ds, $value);
                $re = "#^(?:(?:\\.)|(?:\\.\\.)|(?:\\.?[[:alnum:]](?:[[:punct:]]?[[:alnum:]]+)*)|)\$#";
                foreach($parts as $p) {
                    if(!preg_match($re,$p)) {
                        $err = "malformed path '$value'";
                        return false;
                    }
                }
                // FIXME: this is pretty incomplete
                return true;
            default:
                return false;
        }
    }
    // }}}
}

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
