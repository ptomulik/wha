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

require_once('WHA/Tools.php');

class WHA_PkgQueryToolException { }

/**
 * Functions to query system's package manager for packages' characteristics
 * (installed files, package versions and so on).
 *
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
class WHA_PkgQueryTool
{
    // instance() {{{
    /**
     * @return WHA_PkgQueryTool
     * @since 0.1
     */
    public static function instance() {
        if(!isset(self::$_instance))
            self::$_instance = new WHA_PkgQueryTool();
        return self::$_instance;
    }
    // }}}
    // $_instance {{{
    /**
     * Single instance
     * @var WHA_PkgQueryTool
     * @since 0.1
     */
    private static $_instance;
    // }}}
    // $_supported {{{
    /**
     * List of supported package managers
     * @var arrays
     * @since 0.1
     */
    static private $_supported = array( 
        'dpkg-query',
        'pkg_info'
    );
    // }}}
    // $_available {{{
    /**
     * List of package query tools available on this OS.
     * @var array
     * @since 0.1
     */
    private $_available;
    // }}}
    // $_tool {{{
    /**
     * Currently selected tool
     * @var array
     * @since 0.1
     */
    private $_tool;
    // }}}
    // __construct() {{{
    /**
     * Constructor
     * @since 0.1
     */
    private function __construct()
    {
        $this->discover();
    }
    // }}}
    // discover() {{{
    /**
     * Discover supported package managers available on this OS.
     *
     * @return bool `true` on success of `false` on failure.
     * @since 0.1
     */
    public function discover()
    {
        $this->_available = array();
        foreach (self::$_supported as $tool) {
            if(wha_tool($tool))
                $this->_available[] = $tool;
        }
        if(!isset($this->_tool)) {
            if(count($this->_available) > 0) {
                $this->_tool = $this->_available[0];
            }
        }
    }
    // }}}
    // getSupportedTools() {{{ 
    /**
     * @return array
     * @since 0.1
     */
    public function getSupportedTools() {
        return $this->_supported;
    }
    // }}}
    // getAvailableTools() {{{ 
    /**
     * @return array
     * @since 0.1
     */
    public function getAvailableTools() {
        return $this->_available;
    }
    // }}}
    // getCurrentTool() {{{ 
    /**
     * @return string
     * @since 0.1
     */
    public function getCurrentTool() {
        return $this->_tool;
    }
    // }}}
    // setTool($tool) {{{ 
    /**
     * Set the tool.
     *
     * The `$tool` must be one of available tools ({@link getAvailableTools()})
     * and it must be supported ({@link getSupportedTools()}.
     *
     * @param string
     * @throw {@link WHA_PkgQueryToolException}
     * @since 0.1
     */
    public function setTool($tool) {
        if(!in_array($tool, $this->_available)) {
            if(!in_array($tool, self::$_supported)) {
                $msg = "the selected tool '".$tool."' is not supported";
                throw new WHA_PkgQueryToolException($msg);
            }
            $this->discover();
            // if still nothing
            if(!in_array($tool, $this->_available)) {
                $msg = "the selected tool '".$tool."' is not available";
                throw new WHA_PkgQueryToolException($msg);
            }
        }
        $this->_tool = $tool;
    }
    // }}}
    // isInstalled() {{{
    /**
     * Check whether a package is installed. 
     *
     * Return `true` if package is installed, `false` if not, `null` if there 
     * is no supported tool to perform query or there was a problem with 
     * process initialization.  Error messages go to `$err', standard output 
     * from external tools is used internally or discarded.
     *
     * @param string package name
     * @param string error messages from package query tool
     * @return bool|null
     * @since 0.1
     */
    public function isInstalled($pkg, &$err = null)
    {
        if(!isset($this->_tool)) {
            $err = 'isInstalled(): no tool available to perform query';
            return null;
        }
        switch($this->_tool) {
            case 'pkg_info':
                $args = array('-q', '-G', '-E', $pkg);
                $status = wha_tool_run('pkg_info', $args, null, $out, $err);
                if($status ==  -1) return null;
                elseif($status > 0) return false;
                return true;
            case 'dpkg-query':
                $args = array('-f','${binary:Package} ${Status}', '-W', $pkg);
                $status = wha_tool_run('dpkg-query', $args, null, $out, $err);
                if($status ==  -1) return null;
                elseif($status > 0) return false;
                $fields = explode(' ', $out);
                if(!$fields) return false;
                if(end($fields) !== 'installed') return false;
                return true;
            default:
                $err = 'isInstalled(): no supported tool to perform query';
                return null;
        }
    }
    // }}}
    // listFiles() {{{
    /**
     * List files contained within package.
     *
     * @param string package name
     * @param string error messages from package query tool
     * @return array|false array with file names, or `false` on error
     * @since 0.1
     */
    public function listFiles($pkg, &$err = null)
    {
        if(!isset($this->_tool)) {
            $err = 'listFiles(): no tool available to perform query';
            return null;
        }
        switch($this->_tool) {
            case 'pkg_info':
            case 'dpkg-query':
                $args = array('-L', $pkg);
                $status = wha_tool_run($this->_tool, $args, null, $out, $err);
                if($status !=  0) return false;
                elseif($status > 0) return false;
                $files = explode("\n", $out);
                // remove empty entries
                $files = array_filter($files, function ($f){return (bool)$f;});
                return $files;
            default:
                $err = 'listFiles(): no supported tool to perform query';
                return false;
        }
    }
    // }}}
    // globInstalled() {{{
    /**
     * Find among installed packages the packages matching given glob. Return 
     * array of package names (empty if none found) or false in case of errors.
     *
     * @param string glob string, e.g "apache*"
     * @param string error messages from package query tool
     * @return array | false
     *
     * @since 0.1
     */
    public function globInstalled($glob, &$err = null)
    {
        if(!isset($this->_tool)) {
            $err = 'globInstalled(): no tool available to perform query';
            return false;
        }
        switch($this->_tool) {
            case 'pkg_info':
                $args = array('-E', $glob);
                $status = wha_tool_run('pkg_info', $args, null, $out, $err);
                if($status ==  -1) return false;
                elseif($status > 0) return array();
                return is_string($out) ? explode("\n", $out) : false;
            case 'dpkg-query':
                $args = array('-f','${binary:Package} ${Status}\n','-W',$glob);
                $status = wha_tool_run('dpkg-query', $args, null, $out, $err);

                if($status ==  -1) return false;
                elseif($status > 0 || !$out) return array();
                $names = array();
                $lines = explode("\n", $out);
                foreach($lines as $line) {
                    $fields = explode(' ', $line);
                    if(end($fields) == 'installed') {
                        $names[] = $fields[0];
                    }
                }
                return $names;
            default:
                $err = 'globInstalled(): no supported tool to perform query';
                return false;
        }
    }
    // }}}
    // globReInstalled() {{{
    /**
     * Find among installed packages the packages matching given glob. Return 
     * array of package names found matching given regular expression. Return 
     * false in case of errors.
     *
     * @param string glob string, e.g "apache*"
     * @param string regular expression
     * @param string error messages from package query tool
     * @return array | false
     *
     * @since 0.1
     */
    public function globReInstalled($glob, $re, &$err = null)
    {
        $pkgs = $this->globInstalled($glob, $err);
        if(!is_array($pkgs)) return $pkgs;
        $pkgs = array_filter( $pkgs, function ($p) use ($re) {
            return preg_match($re, $p);
        });
        return $pkgs;
    }
    // }}}
}

/**
 * Short-hand to {@link WHA_PkgQueryTool::isInstalled()}.
 *
 * @param string package name
 * @param string error messages from package query tool
 * @return bool|null
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>                              
 * @since 0.1
 */
function wha_pkg_is_installed($pkg, &$err = null)
{
    return WHA_PkgQueryTool::instance()->isInstalled($pkg, $err);
}
/**
 * Short-hand to {@link WHA_PkgQueryTool::listFiles()}.
 *
 * @param string package name
 * @param string error messages from package query tool
 * @return array | false
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>                              
 * @since 0.1
 */
function wha_pkg_list_files($pkg, &$err = null)
{
    return WHA_PkgQueryTool::instance()->listFiles($pkg, $err);
}
/**
 * Short-hand to {@link WHA_PkgQueryTool::globInstalled()}.
 *
 * @param string glob string, e.g "apache*"
 * @param string error messages from package query tool
 * @return array | false
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>                              
 * @since 0.1
 */
function wha_pkg_glob_installed($glob, &$err = null)
{
    return WHA_PkgQueryTool::instance()->globInstalled($glob, $err);
}
/**
 * Short-hand to {@link WHA_PkgQueryTool::globReInstalled()}.
 *
 * @param string glob string, e.g "apache*"
 * @param string regular expression,
 * @param string error messages from package query tool
 * @return array | false
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>                              
 * @since 0.1
 */
function wha_pkg_glob_re_installed($glob, $re, &$err = null)
{
    return WHA_PkgQueryTool::instance()->globReInstalled($glob, $re, $err);
}

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
