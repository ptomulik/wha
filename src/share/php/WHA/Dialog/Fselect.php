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


require_once('WHA/Dialog/Functions.php');
require_once('WHA/Dialog/Widget.php');

/**
 * Message box dialog.
 *
 * This class implements `fselect` dialog.
 *
 * @author Pawel Tomulik <ptomuilik@meil.pw.edu.pl>
 * @package WHA
 * @since 0.1
 */
class WHA_DialogFselect extends WHA_DialogWidget
{
    // $_value {{{
    /**
     * Initial text shown in the fselect box
     *
     * @var string
     * @since 0.1
     */
    protected $_value;
    // }}}
    // display() {{{
    /**
     * Display fselect.
     *
     * This function does not return until user exits fselect box (OK, CANCEL 
     * ESC). 
     *
     * On exit, the {@link getExitCode()} says whether the user pressed OK (0), 
     * CANCEL (1) or ESC (255). If there is another error (for example wrong 
     * CLI syntax or system error) an exception is raised.
     *
     * @throw {@link WHA_DialogException}
     * @since 0.1
     */
    protected function display() {

        if(is_array($this->options)) $opts = $this->options;
        else $opts = array();

        $out = $this->_output;
        $this->_output = $this->getValue();
        $e = wha_dialog_fselect(
            $this->_output,
            $this->_errmsg,
            $this->text,
            $this->height,
            $this->width,
            $opts
        );
        if($e === DIALOG_OK) $this->setValue($this->_output);
        else $this->_output = $out;

        $this->_done = true;
        return $e;
    }
    // }}}
    // setValue($val) {{{
    /**
     * Set value shown in the fselect box
     *
     * @var string
     * @since 0.1
     */
    public function setValue($val) {
        $this->_value = $val;
    }
    // }}}
    // getValue() {{{
    /**
     * Initial text shown in the fselect box
     *
     * @var string
     * @since 0.1
     */
    public function getValue() {
        return $this->_value;
    }
    // }}}
}


// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
