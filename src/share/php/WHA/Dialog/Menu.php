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

require_once('WHA/Dialog/Widget.php');
require_once('WHA/Dialog/Functions.php');

/**
 * Menu dialog.
 *
 * This class implements menu dialog. As in UNIX `dialog program`, the menu has
 * tags and corresponding items. Tag is a unique string idenifying menu entry.
 * In this implementation we use associative arrays to represent menu entries.
 * We also allow to associate callbacks with tags, so that user functions may
 * be invoked when menu entry is selected.
 *
 * One of the menu entries may act as so-called "return entry". When this entry
 * is pressed, the control is returned from {@link run()} method to the caller
 * and the exit code ({@link getExitCode()}) is set to zero (user pressed OK).
 * To define the return tag, use {@link setReturnTag()}.
 *
 * @author Pawel Tomulik <ptomuilik@meil.pw.edu.pl>
 * @package WHA
 * @since 0.1
 */
class WHA_DialogMenu extends WHA_DialogWidget
{
    static public $default_menu_height = 12;
    static public $default_items = array('EMPTY'=>'Nothing', 'DONE'=>'Done');

    // $menu_height {{{
    /**
     * The `menu_height` parameter to `dialog` command.
     *
     * @var int
     * @since 0.1
     */
    public $menu_height;
    // }}}
    // $items{{{
    /**
     * An associative array of tag => item  pairs which defines menu entries.
     * Tags and items are strings and have meaning as defined in manual page of
     * `dialog` program.
     *
     * @var array
     * @since 0.1
     */
    public $items;
    // }}}
    // $selection {{{
    /**
     * Current selection (tag).
     *
     * @var string
     * @since 0.1
     */
    public $selection;
    // }}}
    protected $_return_tag;
    protected $_menu_callbacks;

    // __construct() {{{
    /**
     * Constructor.
     *
     * @param string the `text` argument for `dialog` command,
     * @param int the `height` argument for `dialog` command,
     * @param int the `width` argument for `dialog` command,
     * @param int the `menu_height` argument for `dialog` command,
     * @param array the `items` argument for `dialog` command,
     * @param array `common_options` to the `dialog`,
     * @param array user callbacks,
     * @param string name of the return tag,
     * @param string initial selection (tag)
     *
     * @since 0.1
     * @throw {@link WHA_DialogException}
     */
    public function __construct($text = null, $height = null, $width = null,
                                $menu_height = null, $items = null,
                                $options = null, $callbacks = null,
                                $rtag = null, $sel = NULL) {

        parent::__construct($text, $height, $width, $options);

        if(isset($menu_height)) $this->menu_height = $menu_height;
        else $this->menu_height = self::default_menu_height;
        if(isset($items)) $this->items = $items;
        else $this->items = self::default_items;
        if(isset($sel)) $this->selection= $sel;

        $this->setMenuCallbacks($callbacks);
        $this->setReturnTag($rtag);
    }
    // }}}
    // display() {{{
    /**
     * Display this widget.
     *
     * @return int exit code from the dialog.
     * @since 0.1
     */
    protected function display() {

        if(is_array($this->options)) $opts = $this->options;
        else $opts = array();

        if(!isset($this->selection)) {
            if(is_array($this->items) && count($this->items) > 0) {
                // FIXME: check if --default-item is not already there
                $sel = array_keys($this->items)[0];
                $opts = array_merge(array('--default-item', $sel), $opts);
            }
        } elseif(in_array($this->selection, array_keys($this->items))) {
            $opts = array_merge(array('--default-item',$this->selection),$opts);
        }

        $e = wha_dialog_menu(
            $this->_output,
            $this->_errmsg,
            $this->text,
            $this->height,
            $this->width,
            $this->menu_height,
            $this->items,
            $opts
        );

        if($e === 0) {
            $this->selection = $this->_output;
            $rt = $this->getReturnTag();
            if($rt && $rt == $this->selection) {
                $this->_done = true;
            }
            if(isset($this->_menu_callbacks[$this->selection])) {
                $cb = $this->_menu_callbacks[$this->selection];
                if(is_callable($cb)) call_user_func($cb, $this);
            }
        } else {
            $this->_done = true;
        }
        return $e;
    }
    // }}}
    // getReturnTag() {{{
    /**
     * Get the return tag for this menu.
     */
    public function getReturnTag() { return $this->_return_tag; }
    // }}}
    // setReturnTag($tag) {{{
    public function setReturnTag($tag) { $this->_return_tag = $tag; }
    // }}}
    // setMenuCallbacks($cbs) {{{
    /**
     * Associate menu items with callbacks.
     *
     * The `$cbs` argument should be an associative array, with keys
     * corresponding to menu tags and values being callable. A callback
     * should accept single argument. The menu object will be passed to
     * callback as an argument. The value returned by callback is ignored.
     *
     * @param array array of callable objects
     * @throw {@link WHA_DialogException}
     * @since 0.1
     */
    public function setMenuCallbacks($cbs) {
        if(isset($cbs) && !is_array($cbs))
            throw new WHA_DialogException('expected array but got '.gettype($cbs));
        if(is_array($cbs)) {
            foreach($cbs as $tag => $cb) {
                if(!is_callable($cb)) {
                    throw new WHA_DialogException('callback is not callable');
                }
            }
        }
        $this->_menu_callbacks = $cbs;
    }
    // }}}
    // getMenuCallbacks($cbs) {{{
    /**
     * Return array of menu callbacks defined with {@link setMenuCallbacks()}.
     *
     * @return array
     * @since 0.1
     */
    public function getMenuCallbacks() {
        return $this->_menu_callbacks;
    }
    // }}}
}


// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
