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


require_once('WHA/Cmd.php');

/**
 * Base class for CLI commands.
 *
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
abstract class WHA_CliCmd implements WHA_ICmd
{
    // $_cli_script {{{
    /**
     * Script name, copied from `$argv[0]`.
     * @var string
     * @since 0.1
     */
    protected $_cli_script;
    // }}}
    // $_cli_cops{{{
    /**
     * Common options (typed before command) received from CLI.
     * @var array
     * @since 0.1
     */
    protected $_cli_cops;
    // }}}
    // $_cli_cmd {{{
    /**
     * Command name received from CLI.
     * @var string
     * @since 0.1
     */
    protected $_cli_cmd;
    // }}}
    // $_cli_opts{{{
    /**
     * Options, received from CLI.
     * @var array
     * @since 0.1
     */
    protected $_cli_opts;
    // }}}
    // $_cli_args{{{
    /**
     * Values of positional arguments received from CLI.
     * @var array
     * @since 0.1
     */
    protected $_cli_args;
    // }}}
    // $_cli_exit_code{{{
    /**
     * Exit code, may be read after command execution completes.
     * @var string
     * @since 0.1
     */
    private   $_cli_exit_code;
    // }}}
    // $_cli_errmsg {{{
    /**
     * Error message, set when command exits with exit code other than zero.
     * @var string
     * @since 0.1
     */
    private   $_cli_errmsg;
    // }}}
    // __construct($script, $cops, $cmd, $opts, $args) {{{
    /**
     * @param string
     * @param array
     * @param string
     * @param array
     * @param array
     * @since 0.1
     */
    public function __construct($script, $cops, $cmd, $opts, $args)
    {
        $this->setCliScriptName($script);
        $this->setCliCommonOptions($cops);
        $this->setCliCommandName($cmd);
        $this->setCliOptions($opts);
        $this->setCliArguments($args);
        $this->_cli_exit_code = 0;
    }
    // }}}
    // getCliScriptName() {{{
    /** 
     * Return script name that was provided from CLI (`$argv[0]`).
     * @return string 
     * @since 0.1
     */
    public function getCliScriptName() { return $this->_cli_script; }
    // }}}
    // getCliCommonOptions() {{{
    /** 
     * Return common options (typed before command) that were found in CLI.
     *
     * @return array 
     * @since 0.1
     */
    public function getCliCommonOptions() { return $this->_cli_cops; }
    // }}}
    // getCliCommandName() {{{
    /**
     * Return name of this command as obtained form CLI.
     * @return string
     * @since 0.1
     */
    public function getCliCommandName() { return $this->_cli_cmd; }
    // }}}
    // getCliOptions() {{{
    /**
     * Return options received form CLI.
     * @return array 
     * @since 0.1
     */
    public function getCliOptions() { return $this->_cli_opts; }
    // }}}
    // getCliArguments()  {{{
    /**
     * Return positional arguments received form CLI.
     * @return array
     * @since 0.1
     */
    public function getCliArguments() { return $this->_cli_args; }
    // }}}
    // setCliScriptName($script) {{{
    /**
     * @param string
     * @since 0.1
     */
    protected function setCliScriptName($script) {
        $this->_cli_script = $script; 
    }
    // }}}
    // setCliCommonOptions($cops) {{{
    /**
     * @param array
     * @since 0.1
     */
    protected function setCliCommonOptions($cops) {
        $this->_cli_cops = $cops;
    }
    // }}}
    // setCliCommandName($cmd) {{{
    /**
     * @param string
     * @since 0.1
     */
    protected function setCliCommandName($cmd) { 
        $this->_cli_cmd = $cmd; 
    }
    // }}}
    // setCliOptions($opts) {{{
    /**
     * @param array
     * @since 0.1
     */
    protected function setCliOptions($opts) { 
        $this->_cli_opts = $opts; 
    }
    // }}}
    // setCliArguments($args) {{{
    /**
     * @param array
     * @since 0.1
     */
    protected function setCliArguments($args) {
        $this->_cli_args = $args; 
    }
    // }}}
    // getCliCommonOption($name) {{{
    /**
     * Returns value of a common option or null.
     *
     * Returns value of a requeset common option `$name` or null, if the option 
     * doesn't exist (was not set on CLI e.g.). 
     *
     * If the requested option is a boolean flag, the function returns `true` 
     * (option was present on CLI) or `null` (option was absent on CLI).
     *
     * @param string option name, e.g. `'-q'`
     * @return mixed|null
     * @since 0.1
     */
    public function getCliCommonOption($name) {
        $cops = $this->getCliCommonOptions();
        if (isset($cops[$name])) return $cops[$name];
        return null;
    }
    // }}}
    // getCliOption($name) {{{
    /**
     * Returns value of an option or null.
     *
     * Returns value of a requeset option `$name` or null, if the option 
     * doesn't exists (was not provided on CLI e.g.).
     *
     * If the requested option is a boolean flag, the function returns `true` 
     * (option was present on CLI) or `null` (option was absent on CLI).
     *
     * @param string option name, e.g. `'-q'`
     * @return mixed|null
     * @since 0.1
     */
    public function getCliOption($name) {
        $opts = $this->getCliOptions();
        if (isset($opts[$name])) return $opts[$name];
        return null;
    }
    // }}}
    // getCliArgument($n) {{{
    /**
     * Return `$n`'th positional argument obtained from CLI. If such argument 
     * doesn't exist, null is returned.
     *
     * @param int index of the requested argument
     * @return mixed|null
     * @since 0.1
     */
    public function getCliArgument($n) {
        if(isset($this->_cli_args[$n])) return $this->_cli_args[$n];
        return null;
    }
    // }}}
    // getCliArgCount() {{{
    /**
     * Return number of the CLI arguments available.
     *
     * @param int index of the requested argument
     * @return string|null
     * @since 0.1
     */
    public function getCliArgCount($n) {
        if(!is_array($this->_cli_args)) return 0;
        return count($this->_cli_args);
    }
    // }}}
    // execute() {{{
    /**
     * Execute this command.
     *
     * This function takes no arguments and returns no value (the exit code
     * from command may be obtained by {@link getCliExitCode()}).
     * @since 0.1
     */
    abstract public function execute();
    // }}}
    // getCliExitCode() {{{
    /**
     * Get command's exit code.
     *
     * This is the code that should be returned to parent process (shell).
     *
     * @return int 
     * @since 0.1
     */
    public function getCliExitCode() { return $this->_cli_exit_code; }
    // }}}
    // setCliExitCode($code) {{{
    /**
     * @param int
     * @return int Integer exit code to return to parent shell.
     * @since 0.1
     */
    protected function setCliExitCode($code) { $this->_cli_exit_code = $code; }
    // }}}
    // getCliErrorMessage() {{{
    /**
     * Get command's exit code.
     *
     * This is the code that should be returned to parent process (shell).
     *
     * @return string|null
     * @since 0.1
     */
    public function getCliErrorMessage() { return $this->_cli_errmsg; }
    // }}}
    // setCliErrorMessage() {{{
    /**
     * @param int
     * @return string   Error message 
     * @since 0.1
     */
    protected function setCliErrorMessage($msg) { $this->_cli_errmsg = $msg; }
    // }}}
}


// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
