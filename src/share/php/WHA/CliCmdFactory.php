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


require_once('WHA/Cli.php');
require_once('WHA/CliCmd.php');

class WHA_CliCmdFactoryException extends Exception { };
class WHA_CliCmdNotRegistered extends WHA_CliCmdFactoryException { };
class WHA_CliCmdAlreadyRegistered extends WHA_CliCmdFactoryException { };

/**
 * Abstract factory of {@link WHA_CliCmd} ojects.
 *
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
class WHA_CliCmdFactory
{
    // $_classes {{{
    /**
     * Associative array of registered classes. 
     *
     * Each item is of the form $cmd => $class, where $cmd is command name, 
     * and $class is (string) class name which implements given command.
     * @var array
     * @since 0.1
     */
    private $_classes;
    // }}}
    // $_cli {{{
    /**
     * WHA_Cli object associated with this factory.
     * @var WHA_Cli
     * @since 0.1
     */
    private $_cli;
    // }}}
    // $_instance {{{
    /**
     * The unique instance of the factory object.
     * @var WHA_CliCmdFactory
     * @since 0.1
     */
    private static $_instance = null;
    // }}}

    // instance() {{{
    /**
     * Get the single instnace of {@link WHA_CliCmdFactory}
     * @since 0.1
     */
    public static function instance($cli = null) {
        if(!isset(self::$_instance)) {
            self::$_instance = & new WHA_CliCmdFactory($cli);
        }
        return self::$_instance;
    }
    // }}}
    // getCli() {{{
    /**
     * Get the {@link WHA_Cli} instance used by this factory.
     *
     * @return WHA_Cli
     * @since 0.1
     */
    public function getCli() { return $this->_cli; }
    // }}}
    // hasRegistered($cmd) {{{
    /**
     * Check if given command is already registered.
     *
     * @param string command name
     * @return bool
     * @since 0.1
     */
    public function hasRegistered($cmd) {
        return isset($this->_classes[$cmd]);
    }
    // }}}
    // registerCmd($class, $cmd, $opts, $args) {{{
    /**
     * @param string    name of the class implementing given command,
     * @param string    command name,
     * @param array     description of the command, this is value of single 
     *                  item for {@link WHA_Cli::$cmds} 
     * @param array     definition of command options, this is value of single 
     *                  item for {@link WHA_Cli::$opts}
     * @param array     definition of positional arguments, this is value of 
     *                  single item for {@link WHA_Cli::$args}
     *
     * @throw {@link WHA_CliCmdAlreadyRegistered},
     *        {@link  WHA_CliCmdFactoryException}
     * @since 0.1
     */
    public function registerCmd($class, $cmd, $info, $opts=null, $args=null)
    {
        // FIXME: replace this with some exceptions?
        if(!class_exists($class)) {
            $msg = "class \'". $class . "\' does not exist";
            throw WHA_CliCmdFactoryException($msg);
        }
        if(!is_subclass_of($class, 'WHA_CliCmd')) {
            $msg = "\'". $class . "\' is not a subclass of WHA_CliCmd";
            throw WHA_CliCmdFactoryException($msg);
        }
        if(!is_string($cmd)) {
            $msg = '$cmd must be a string, but' ."'".gettype($cmd)."' given";
            throw WHA_CliCmdFactoryException($msg);
        }
        $this->_checkNotRegistered($cmd);

        $this->_classes[$cmd] = $class;
        $this->_cli->cmds[$cmd] = $info;
        if(isset($opts)) $this->_cli->opts[$cmd] = $opts;
        if(isset($args)) $this->_cli->args[$cmd] = $args;
    }
    // }}}
    // unregisterCmd($cmd) {{{
    /**
     * @param string        command name,
     * @since 0.1
     */
    public function unregisterCmd($cmd)
    {
        if(!$this->hasRegistered($cmd)) return;

        if(isset($this->_classes[$cmd])) unset($this->_classes[$cmd]);
        if(isset($this->_cli->cmds[$cmd])) unset($this->_cli->cmds[$cmd]);
        if(isset($this->_cli->opts[$cmd])) unset($this->_cli->opts[$cmd]);
        if(isset($this->_cli->args[$cmd])) unset($this->_cli->args[$cmd]);
    }
    // }}}
    // getCmdClass($cmd) {{{
    /**
     * @param string command name
     * @return WHA_CliCmd
     * @throw {@link WHA_CliCmdNotRegistered}
     * @since 0.1
     */
    public function getCmdClass($cmd) {
        $this->_checkRegistered($cmd);
        return $this->_classes[$cmd];
    }
    // }}}
    // createCmdFromArgv($argv, &$err = null) {{{
    /**
     * Create command object {@link WHA_CliCmd} according to command-line 
     * values given given in `$argv`.
     *
     * On success returns {@link WHA_CliCmd} object. If `$argv` is invalid, 
     * returns `false`. If some error in logics (bug) occurrs,
     * {@link WHA_CliCmdFactoryException} might be thrown.
     *
     * @param array same as in {@link WHA_Cli::parseArgv()}
     * @param string same as in {@link WHA_Cli::parseArgv()}
     *
     * @return WHA_CliCmd|false
     * @throw {@link WHA_CliCmdNotRegistered} (only if there is some bug)
     * @since 0.1
     */
    public function createCmdFromArgv($argv, &$err = null)
    {
        $x = $this->_cli->parseArgv($argv, $err);
        if($x === FALSE) return FALSE;
        list($scr, $cops, $cmd, $opts, $args) = $x; 
        // The exception should newer happen. If it does, that means that the 
        // arrays $this->_classes and $this->_cli->cmds are inconsistent (bug).
        $class = $this->getCmdClass($cmd);
        return new $class($scr, $cops, $cmd, $opts, $args);
    }
    // }}}
    // __construct($cli == null) {{{
    /**
     * @param WHA_Cli|null instance of {@link WHA_Cli}
     * @since 0.1
     */
    private function __construct($cli = null) {
        $this->_classes = array();
        if($cli === null)
            $this->_cli = & new WHA_Cli();
        else
            $this->_cli = $cli;
    }
    // }}}
    // _checkRegistered($cmd) {{{
    /**
     * @param string command name
     * @throw {@link WHA_CliCmdNotRegistered}
     * @since 0.1
     */
    private function _checkRegistered($cmd) {
        if(!$this->hasRegistered($cmd)) {
            if(is_string($cmd)) $msg = "command '".$cmd."' is not registered";
            else $msg = "requested command is not registered";
            throw new WHA_CliCmdNotRegistered($msg);
        }
    }
    // }}}
    // _checkNotRegistered($cmd) {{{
    /**
     * @param string command name
     * @throw {@link WHA_CliCmdAlreadyRegistered}
     * @since 0.1
     */
    private function _checkNotRegistered($cmd) {
        if($this->hasRegistered($cmd)) {
            if(is_string($cmd)) $msg = "command '".$cmd."' already registered";
            else $msg = "command already registered";
            throw new WHA_CliCmdAlreadyRegistered($msg);
        }
    }
    // }}}
};


// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
