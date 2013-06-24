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


// Try to include PEAR::Config.
$__wha_21dad038 = error_reporting(0);
$__wha_e592e41b = include_once('Config.php');
error_reporting($__wha_21dad038);
if(!$__wha_e592e41b) {
    trigger_error('PEAR::Config is not installed.', E_USER_ERROR);
}
unset($__wha_e592e41b, $__wha_21dad038);

require_once('WHA/PkgQueryTool.php');

class WHA_ConfEditException extends Exception {};
/**
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
class WHA_ConfEdit {
    // $_defaults {{{
    /**
     * Default values for configuration directives
     *
     * @var array
     * @since 0.1
     */
    protected static $_defaults = array(
        'apache' => array(
            'pkgname' => '',
            'confdir' => '',
            'vhosts-available' => '',
            'vhosts-enabled' => '',
            'macros' => '',
        ),
        'log-rotation' => array(
            'facility' => ''
        )
    );
    // }}}
    // $_conf {{{
    /**
     * @var Config (from PEAR::Config package)
     * @since 0.1
     */
    protected $_conf;
    // }}}
    // $_curr_file {{{
    /**
     * @var string absolute path to the currently edited file
     * @since 0.1
     */
    protected $_curr_file;
    // }}}
    // __construct() {{{
    /**
     * Constructor
     * @since 0.1
     */
    public function __construct() {
        $this->resetConfig();
    }
    // }}}
    // resetConfig($file) {{{
    /**
     * Initialize Config with default values.
     *
     * @since 0.1
     */
    public function resetConfig() {
        $this->_conf = new Config();
        $root = $this->_conf->getRoot();

        foreach(self::$_defaults as $section => $items) {
            $node = $root->createSection($section);
            foreach($items as $key => $value) {
                $node->createDirective($key, $value);
            }
        }

        return true;
    }
    // }}}
    // loadIniFile($file) {{{
    /**
     * Load *.ini file to this object. Return `true` on success `PEAR_Error` on 
     * failure. 
     *
     * @param string path to the config file
     * @return bool|PEAR_Error 
     * @since 0.1
     */
    public function loadIniFile($file) {
        $conf = new Config();
        $root =& $conf->parseConfig($file, 'IniCommented');
        if(!($root instanceof Config_Container)) return $root;
        if(isset($this->_conf)) unset($this->_conf);
        $this->_conf = $conf;
        $this->_curr_file = realpath($file);
        return true;
    }
    // }}}
    // saveIniFile($file = null) {{{
    /**
     * Save current configuration to file.
     *
     * On success return `true`, on error `PEAR_Error` is returned. If the 
     * object is not initialized, exception is thrown.
     * `false`.
     *
     * @param string|null path to the output file, if not given, current file
     *                    is used ({@link getCurrentFile()}).
     * @return bool|PEAR_Error
     * @throw WHA_ConfEditException
     * @since 0.1
     */
    public function saveIniFile($file = null) {
        if(!isset($this->_conf)) {
            $msg = "WHA_ConfEdit::saveIniFile(): object is uninitialized";
            throw WHA_ConfEditException($msg);
        }
        if(isset($file)) $new_file = $file;
        else $file = $this->_curr_file;
        $ret = $this->_conf->writeConfig($file,'IniCommented');
        if($ret !== true) return $ret;
        if(isset($new_file)) $this->_curr_file = realpath($new_file);
        return true;
    }
    // }}}
    // getCurrentFile() {{{
    /**
     * @return string
     * @since 0.1
     */
    public function getCurrentFile() {
        return $this->_curr_file;
    }
    // }}}
    // getConfig() {{{
    /**
     * @return Config|null
     * @since 0.1
     */
    public function getConfig() {
        return $this->_conf;
    }
    // }}}
    // searchItem($args) {{{
    /**
     * Shorthand to Config_Container::searchPath(). Search config for an item. 
     *
     * This method tries to find an item by following a given path from the 
     * current container.
     *
     * This method takes as many parameters as is needed to define your path to 
     * the requested item. The format is array (item1, item2, ..., itemN). 
     * Items can be strings or arrays. Strings will match the item name, while 
     * arrays will match 'name' and/or 'attributes' properties of the requested 
     * item.
     *
     * @param mixed Array of strings or arrays of items to match in the order 
     *              they will be matched, separated by commas. It may also be 
     *              a string in form `parent/child`.
     * @return mixed reference to item found or `false` when not found, 
     *              PEAR_Error if `$args` is of invalid type.
     * @since 0.1
     */
    public function searchItem($args) {
        $root = $this->_conf->getRoot();
        if(is_string($args)) {
            $args = explode('/', $args);
        }
        $item = $root->searchPath($args);
        return $item;
    }
    // }}}
    // searchItemContent($args) {{{
    /**
     * Find a config item and return it's content. 
     *
     * @param mixed Strings or arrays of item to match in the order they will 
     *              be matched, separated by commas
     * @return mixed value of the item found or `null` if not found.
     * @since 0.1
     */
    public function searchItemContent($args) {
        $item = $this->searchItem($args);
        if($item === false) return null;
        return $item->getContent();
    }
    // }}}
    // findInstalledApaches() {{{
    /**
     * Find the name(s) of installed apache package(s).
     *
     * @return array|false  array of strings if package(s) found, empty array 
     *                      if not, `false` if an error occurred.
     * @since 0.1
     */
    public function findInstalledApaches(&$err = null) {
        $glob = 'apache*';
        $re = '/apache[0-9]{0,2}(?:-[0-9]{1,2}(?:[\._][0-9]{1,2})*)?$/';
        return wha_pkg_glob_re_installed($glob, $re, $err);
    }
    // }}}
    // findAvailLogrotFacility() {{{
    /**
     * Find the available log rotation facility.
     *
     * @return string|false  string if found or `false` if not
     * @since 0.1
     */
    public function findAvailLogrotFacility(&$err = null) {
        $sel = false;
        $facilities = array('logrotate', 'newsyslog');
        foreach($facilities as $facility) {
            $result = wha_tool_run('which',array($facility),null,$out,$err);
            if($result == 0 && !$err && is_string($out) && strlen($out) > 0) {
                $sel = $facility;
                break;
            } 
        }
        return $sel;
    }
    // }}}

}


// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
