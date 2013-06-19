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


require_once('WHA/Misc.php');

define('WHA_TOOLS_WIN32', defined('OS_WINDOWS') ? OS_WINDOWS : !strncasecmp(PHP_OS, 'win', 3));

/**
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
class WHA_ToolsException extends Exception { }

/**
 * Registry of available tools (external programs) that may be executed by WHA.
 * It maps tool names (independent on actual program name or  its location in 
 * filesystem) onto absolute paths to executables.
 *
 * By default, initial configuration is loaded from tools.ini ([tools] 
 * section).
 * 
 *
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
class WHA_Tools 
{
    // instance() {{{
    /**
     * @return WHA_Tools
     * @since 0.1
     */
    public static function instance() {
        if(!isset(self::$_instance))
            self::$_instance = new WHA_Tools();
        return self::$_instance;
    }
    // }}}
    // $_instance {{{
    /**
     * @var WHA_Tools
     * @since 0.1
     */
    private static $_instance = null;
    // }}}
    // $_tools {{{
    /**
     * @var array
     * @since 0.1
     */
    private static $_tools = null;
    // }}}
    // __construct ($file = 'tools.ini') {{{
    /**
     * @param string|null   name of the configuration file to initially read 
     *                      the list of tools from (default: 'tools.ini')
     * @throw WHA_ToolsException (if there is problem with tools.ini file)
     * @since 0.1
     */
    private function __construct($file = 'tools.ini') {
        $this->_tools = array();
        if (isset($file)) $this->readIniFile($file);
        else $this->_tools = array();
    }
    // }}}
    // isAbspath() {{{
    /**
     * Check whether the `$path` represents absolute path.
     * @return bool
     * @since 0.1
     */
    public static function isAbspath($path) {
        if (preg_match('/(?:\/|\\\)\.\.(?=\/|$)/', $path)) {
            return false;
        }
        if (WHA_TOOLS_WIN32) {
            return (($path{0} == '/') ||  
                     preg_match('/^[a-zA-Z]:(\\\|\/)/', $path));
        }
        return ($path{0} == '/') || ($path{0} == '~');
    }
    // }}}
    // readIniFile() {{{
    /**
     * @param string
     * @throw WHA_ToolsException
     * @since 0.1
     */
    public function readIniFile($file) {
        if (is_file($file))
            $located = $file;
        else
            $located = wha_locate_wha_config($file);
        if ($located === false) {
            $err = "can't locate file '$file'";
            throw new WHA_ToolsException($err);
        }
        $file = $located;
        $ini = parse_ini_file($file, true);
        if ($ini === false) {
            $err = "can't parse file '$file'";
            throw new WHA_ToolsException($err);
        }

        if (!array_key_exists('tools', $ini)) {
            $err = "no section [tools] in '$file'";
            throw new WHA_ToolsException($err);
        }

        foreach($ini['tools'] as $tool => $path) {
            if ($path) { // ignore empty variables
                try {
                    $this->setTool($tool, $path);
                } catch(WHA_ToolsException $e) {
                    $err = $e->getMessage() .", check your config in '$file'";
                    throw new WHA_ToolsException($err);
                }
            }
        }
    }
    // }}}
    // getTool($name) {{{
    /**
     * @param string tool name
     * @return string absolute path to the tool
     * @since 0.1
     */
    public function getTool($name){
        if(isset($this->_tools[$name])) return $this->_tools[$name];
        return null;
    }
    // }}}
    // setTool($name, $path) {{{
    /**
     * @param string tool name
     * @param string absolute path to the tool
     * @throw {@link WHA_ToolsException} (if the given $path is not executable)
     * @since 0.1
     */
    public function setTool($name, $file){
        $err = "'$file' is neither an executable file nor command";
        if(!self::isAbspath($file) && !file_exists($file)) {
            // Search in system path
            $sfile = null;
            $syspath = getenv('PATH');
            if($syspath) {
                $paths = explode(PATH_SEPARATOR, $syspath);
                foreach($paths as $path) {
                    $path = rtrim($path, DIRECTORY_SEPARATOR);
                    if(is_file($path . DIRECTORY_SEPARATOR . $file))  {
                        $sfile = $path . DIRECTORY_SEPARATOR . $file;
                        break;
                    }
                }
                $file = $sfile;
            }
        }
        if(!is_file($file))
            throw new WHA_ToolsException($err);
        $file = realpath($file); // cope with symbolic links etc..
        if(!is_executable($file))
            throw new WHA_ToolsException($err);

        $this->_tools[$name] = $file;
    }
    // }}}
    // setTools($tools) {{{
    /**
     * @param array associative array with tool names as keys and paths as 
     *              values
     * @throw {@link WHA_ToolsException} (if some tool is not executable)
     * @since 0.1
     */
    public function setTools($tools){
        foreach($tools as $tool => $path) {
            $this->setTool($tool,$path);
        }
    }
    // }}}
    // unsetTool($name) {{{
    /**
     * @param string tool name
     * @since 0.1
     */
    public function unsetTool($name){
        if(isset($this->_tools[$name]))
            unset($this->_tools[$name]);
    }
    // }}}
}

/**
 * Get absolute path to a tool identified by $tool.
 *
 * @param string tool identifier
 * @return string|null absolute path to tool, or null if tool is not defined
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_tool($tool)
{
    return WHA_Tools::instance()->getTool($tool);
}

/**
 * Start $tool program and return resource object representing the new process.
 * After this, you should be able to write to `$pipes[0]` (stdin) and read from 
 * `$pipes[1]` (stdout) and `$pipes[2]` (stderr) to communicate with the new
 * running process.
 *
 * @note Arguments from `$args` are escaped with `escapeshellarg()`.
 *
 * @param string tool name
 * @param array options/arguments to the program,
 * @param array pipes for communication with program,
 * @param file file descriptor to bind to programs's STDIN,
 * @param file descriptor to redirect programs's stdout to
 * @param file descriptor to redirect programs's stderr to
 * @return resource resource returned by `proc_open()`
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_tool_open($tool, $args, &$pipes, $stdin = STDIN, $stdout = STDOUT,
                       $stderr = STDERR)
{
    $descriptorspec = array(
        0 => $stdin, 
        1 => $stdout,
        2 => $stderr
    );

    $prog = wha_tool($tool);
    // Escape arguments, when necessary
    $noescape = ':^[a-zA-Z0-9_=-]+$:';
    $args = array_map(function($a) use ($noescape) { 
        return preg_match($noescape,$a) ? $a : escapeshellarg($a);
    }, $args);
    $toolcom = $prog . ' ' . implode(' ', $args);
    return proc_open($toolcom, $descriptorspec, $pipes);
}

/**
 * Wait for the program process to complete.
 *
 * Wait for program to complete and read its stdout and stderr. Return 
 * programs' exit code (or -1 if there is an error on PHP side).
 *
 * @note Arguments from `$args` are escaped with `escapeshellarg()`.
 *
 * @param resource      resource representing program process,
 *                      as returned by `proc_open()`
 * @param array         pipes for communication with program, as returned by 
 *                      wha_tool_open
 * @param string|false  content retrieved from program's stdout or `false` on 
 *                      failure
 * @param string|false  content retrieved from program's stderr or error 
 *                      message if there is an error on PHP side; `false` if 
 *                      there was error related to reading from pipe
 * @return int          exit code from program (>=0) or -1 if proc_close() 
 *                      fails
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_tool_close($process, $pipes, &$out=null, &$err=null)
{
    if(!is_resource($process)) {
        $err .= 'wha_tool_close(): $process is not a resource';
        return -1;
    }

    if(isset($pipes[0])) {
        fclose($pipes[0]);
        usleep(2000);
    }

    if(isset($pipes[1])) {
        $out = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
    }

    if(isset($pipes[2])) {
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
    }

    $res = proc_close($process);
    if($res == -1) {
        if(!is_string($err)) $err = '';
        if(strlen($err) > 0) $err .= "\nerror: ";
        $err .= "wha_tool_close(): proc_close() returned -1";
    }
    return $res;
}

/**
 * Run tool program.
 *
 * Execute program `$tool`, write `$in` to its STDIN, read program's 
 * output (stdout) to `$out` and error output (stderr) to `$err`.
 *
 * @note Each argument in `$args` will be mapped through `escapeshellarg()`.
 *
 * @param string        tool name
 * @param array         command-line arguments to the program,
 * @param string        content to be pumped to program's stdin
 * @param string|false  content retrieved from program's stdout or `false` on 
 *                      failure
 * @param string|false  content retrieved from program's stderr or `false` on 
 *                      failure
 * @return int      status code from the program (>=0) or -1 in case of other 
 *                  failures (process initialization error, and so on.)
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
function wha_tool_run($tool, $args, $in = null, &$out=null, &$err=null)
{
    $process = wha_tool_open($tool, $args, $pipes, array('pipe', 'r'),
                              array('pipe', 'w'), array('pipe','w'));
    if(!is_resource($process)) {
        $stderr = "wha_tool_run(): can't create process for tool '$tool'";
        return -1;
    }

    if($in) fwrite($pipes[0], $in);
    return wha_tool_close($process, $pipes, $out, $err);
}

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
