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


class WHA_DialogException extends Exception { }

/**
 * Base class for all other dialog widgets.
 *
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
abstract class WHA_DialogWidget
{
    // $default_text {{{
    /**
     * Default value for the `text` argument to `dialog`.
     * @var string
     * @since 0.1
     */
    public static $default_text = 'Default text';
    // }}}
    // $default_height {{{
    /**
     * Default value for the `height` argument to `dialog`.
     * @var int
     * @since 0.1
     */
    public static $default_height = 15;
    // }}}
    // $default_width {{{
    /**
     * Default value for the `width` argument to `dialog`.
     * @var int
     * @since 0.1
     */
    public static $default_width = 60;
    // }}}
    // $default_options {{{
    /**
     * Default set of common options for the `dialog` program.
     * @var array
     * @since 0.1
     */
    public static $default_options = array();
    // }}}
    // $text {{{
    public $text;
    // }}}
    // $height {{{
    /**
     * The `height` argument to `dialog` program.
     * @var int
     * @since 0.1
     */
    public $height;
    // }}}
    // $width {{{
    /**
     *
     * The `width` argument to `dialog` program.
     * @var int
     * @since 0.1
     */
    public $width;
    // }}}
    // $options {{{
    public $options;
    // }}}
    // $_done {{{
    /**
     * If job is done (`$this->_done == true`), stop displaying the widget.
     * @var bool
     * @since 0.1
     */
    protected $_done;
    // }}}
    // $_exit_code {{{
    protected $_exit_code;
    // }}}
    // $_output {{{
    /**
     * Output from the most recent invocation of `dialog` program.
     * @var string 
     * @since 0.1
     */
    protected $_output;
    // }}}
    // $_errmsg {{{
    /**
     * Error message received from most recent execution of `dialog` program. 
     * @since 0.1
     */
    protected $_errmsg;
    // }}}
    // $_btn_callbacks {{{
    /**
     * Callback for buttons.
     *
     * It should be associative array, which maps `dialog`'s button return 
     * codes onto callable objects. Each callable should accept single 
     * argument, which is an instance of calling widget (this object).
     *
     * @var array
     * @since 0.1
     */
    protected $_btn_callbacks;
    // }}}
    // __construct($text=null, $height=null, $width=null, $opts = null) {{{
    /**
     * Constructor
     *
     * @param string the `text` argument to be passed to the `dialog` program,
     * @param int the `height` argument to the `dialog` program,
     * @param int the `width` argument to the `dialog` program,
     * @param array common options to be passed to the `dialog` program
     * @since 0.1
     */
    public function __construct($text=null, $height=null, $width=null, 
                                $opts=null) {
        if(isset($text)) $this->text = $text;
        else $this->text = self::$default_text;
        if(isset($height)) $this->height = $height;
        else $this->height = self::$default_height;
        if(isset($width)) $this->width = $width;
        else $this->width = self::$default_width;
        if(isset($opts)) $this->options = $opts;
        else $this->options = self::$default_options;

        $this->_exit_code = null;
        $this->_output = null;
        $this->_errmsg = null;
    }
    // }}}
    // run() {{{
    /**
     * Run the dialog. If error occurs, throw exception. 
     *
     * @throw WHA_DialogException
     * @since 0.1
     */
    public function run() {
        $this->_done = false;
        while (!$this->_done) {
            $e = $this->display();
            $this->_exit_code = $e;
            if($e === DIALOG_ESC  && $this->hasError()) {
                throw new WHA_DialogException($this->getErrorMessage());
            }
            if(is_int($e) && isset($this->_btn_callbacks[$e])) {
                call_user_func($this->_btn_callbacks[$e], $this);
            }
            if(!in_array($e, array(DIALOG_OK,DIALOG_CANCEL,DIALOG_HELP,
                                   DIALOG_EXTRA, DIALOG_ESC))) {
                // avoid unexpected lock-outs
                $this->_done = true;
            }
        }
    }
    // }}}
    // display() {{{
    /**
     * Display this widget.
     *
     * @return int exit code from the dialog.
     * @since 0.1
     */
    abstract protected function display();
    // }}}
    // getExitCode() {{{
    /**
     * Get the exit code returned recently by `dialog` program.
     *
     * @return int
     * @since 0.1
     */
    public function getExitCode() { return $this->_exit_code; }
    // }}}
    // getOutput() {{{
    /**
     * Get output produced by last execution of `dialog` command.
     *
     * @return string | null
     * @since 0.1
     */
    public function getOutput() { return $this->_output; }
    // }}}
    // getErrorMessage() {{{
    /**
     * If {@link hasError()} returned `true`, this function contains returns 
     * error message. 
     *
     * @return string
     * @since 0.1
     */
    public function getErrorMessage() { return $this->_errmsg; }
    // }}}
    // hasError() {{{
    /**
     * Return true, if the last interaction with `dialog` program ended with 
     * error.
     *
     * In that case {@link getErrorMessage()} contains the error message.
     *
     * @return bool
     * @since 0.1
     */
    public function hasError() {
        return is_string($this->_errmsg) && strlen($this->_errmsg) > 0;
    }
    // }}}
    // pressEscape() {{{
    /**
     * Simulate ESC key.
     *
     * This function is mainly for use from callbacks. Its effective only when 
     * invoked from within {@link run()}. After this, the {@linkn run()} 
     * returns control to its caller.
     * 
     * @since 0.1
     */
    public function pressEscape() {
        $this->_exit_code = 255;
        $this->_done = 0;
    }
    // }}}
    // pressCancel() {{{
    /**
     * Simulate Cancel button.
     *
     * This function is mainly for use from callbacks. Its effective only when 
     * invoked from within {@link run()}. After this, the {@linkn run()} 
     * returns control to its caller.
     * 
     * @since 0.1
     */
    public function pressCancel() {
        $this->_exit_code = 1;
        $this->_done = 0;
    }
    // }}}
    // pressOk {{{
    /**
     * Simulate Ok button.
     *
     * This function is mainly for use from callbacks. Its effective only when 
     * invoked from within {@link run()}. After this, the {@linkn run()} 
     * returns control to its caller.
     * 
     * @since 0.1
     */
    public function pressOk() {
        $this->_exit_code = 0;
        $this->_done = 0;
    }
    // }}}
    // resume {{{
    /**
     * Continue displaying widget (e.g. after the button was pressed).
     *
     * This function is mainly for use from callbacks. Its effective only when 
     * invoked from within {@link run()}. After this, the {@linkn run()} 
     * returns control to its caller.
     * 
     * @since 0.1
     */
    public function resume() {
        $this->_done = 0;
    }
    // }}}
    // setBtnCallbacks($cbs) {{{
    /**
     * Associate buttons with callbacks.
     *
     * The `$cbs` argument should be an associative array, with keys 
     * corresponding to `dialog`'s button codes (OK=0, CANCEL=1, HELP=2, 
     * EXTRA=3, ESC=255), and values being callable objects. A callback should 
     * accept single argument. The widget object will be passed to callback as 
     * an argument. The value returned by callback is ignored. 
     *
     * @param array array of callable objects
     * @throw {@link WHA_DialogException}
     * @since 0.1
     */
    public function setBtnCallbacks($cbs) {
        if(isset($cbs) && !is_array($cbs)) 
            throw new WHA_DialogException('expected array but got '.gettype($cbs));
        if(is_array($cbs)) {
            foreach($cbs as $tag => $cb) {
                if(!is_callable($cb)) {
                    throw new WHA_DialogException('callback is not callable');
                }
            }
        }
        $this->_btn_callbacks = $cbs;
    }
    // }}}
}


// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
