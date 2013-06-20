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


require_once('WHA/CliCmd.php');
require_once('WHA/CliCmdFactory.php');
require_once('WHA/Dialog/Dselect.php');
require_once('WHA/Dialog/Menu.php');
require_once('WHA/Dialog/Yesno.php');
require_once('WHA/Dialog/Msgbox.php');
require_once('WHA/Dialog/Inputbox.php');
require_once('WHA/Dialog/Msgbox.php');
require_once('WHA/Dialog/Radiolist.php');
require_once('WHA/Setup.php');
require_once('WHA/Misc.php');

/**
 * Provides the 'setup' command. 
 *
 * This object implements 'wha setup' CLI command. It drives a series of menus 
 * and other dialogs to lead user through configuration steps. 
 *
 * Most of the stuff is based on callbacks. All the methods named `onXxx()` are 
 * callbacks which are called in response to certain menus or buttons. 
 *
 * @package WHA
 * @author Pawel Tomulik <ptomulik@meil.pw.edu.pl>
 * @since 0.1
 */
class WHA_CliCmdSetup extends WHA_CliCmd
{
    // $_cmd_info {{{
    private static $_cmd_info = array(
        'purp' => 'adjust wha configuration to your needs',
        'help' => 
        "
    This is an interactive command which helps you configure your WHA 
    installation. It reads initial configuration from current config file 
    (usually `/etc/wha/wha.ini'), guides you through several configruation 
    steps and finally allows you to save new version of the configuration file. 
    You may provide --input option to read initial configuratoin from custom 
    file. Target file for the new configuration may be defined with --output 
    option.
    ");
    // }}}
    // $_cmd_opts {{{
    private static $_cmd_opts = array(
        '--file' => array(
            'type' => 'file',
            'name' => 'file',
            'help' => 'use `file\' instead of default configuration file',
        )
    );
    // }}}
    // $_cmd_args {{{
    private static $_cmd_args = null;
    // }}}
    // $_setup {{{
    /**
     * An instance of `WHA_Setup` used as an functional backend.
     *
     * @var WHA_Setup
     * @since 0.1
     */
    protected $_setup;
    // }}}
    // $_default_btitle {{{
    /**
     * Default string for {@link _btitle}. 
     * @var string
     * @since 0.1
     */
    public static $default_btitle = "WHA Setup";
    // }}}
    // $_btitle {{{
    /**
     * String used as --backtitle for all the widgets displayed by this object.
     * @var string
     * @since 0.1
     */
    public $_btitle;
    // }}}
    // registerThisCmd() {{{
    public static function registerThisCmd() {
        $cf = WHA_CliCmdFactory::instance();
        if(!$cf->hasRegistered('setup'))
            $cf->registerCmd('WHA_CliCmdSetup', 'setup', self::$_cmd_info,
                             self::$_cmd_opts, self::$_cmd_args);
    }
    // }}}
    // execute() {{{
    public function execute()
    {
        $file = $this->getCliOption('--file');
        if(isset($file)) {
            if(!file_exists($file)) {
                $text = "The file '".$file."' does not exist. Create?";
                $opts = array('--backtitle', self::$default_btitle,
                              '--title', 'Initial configuration');
                $dialog = new WHA_DialogYesno($text, 7, 60, $opts);
                $dialog->run();
                if($dialog->getExitCode() != DIALOG_OK) {
                    return;
                } else {
                    $this->_setup = new WHA_Setup();
                    $ok = $this->_setup->saveIniFile($file);
                    if($ok !== true) {
                        $this->setCliErrorMessage($ok->getMessage());
                        $ok = false;
                    }
                }
            } else {
                if(!is_readable($file)) {
                    $this->setCliErrorMessage('file '.$file.' is not readable');
                    $ok = false;
                } else {
                    $this->_setup = new WHA_Setup();
                    $ok = $this->_setup->loadIniFile($file);
                    if($ok !== true)
                        $this->setCliErrorMessage($ok->getMessage());
                }
            }
        } else {
            $located = wha_locate_wha_config('wha.ini');
            if($located === false) {
                $text = "Can't locate default configuration file 'wha.ini'";
                $this->setCliErrorMessage($text);
                $ok = false;
            } else {
                $this->_setup = new WHA_Setup();
                $ok = $this->_setup->loadIniFile($located);
                if($ok !== true)
                    $this->setCliErrorMessage($ok->getMessage());
            }
        }

        if($ok !== true) { // $ok can be PEAR_Error!
            $opts = array('--backtitle', self::$default_btitle, 
                          '--title', 'Error');
            $this->setCliExitCode(1);
            $msg = $this->getCliErrorMessage();
            $dialog = new WHA_DialogMsgbox($msg, 7, 60, $opts);
            return;
        }

        $this->_btitle = self::$default_btitle ; 
        $curfile = $this->_setup->getCurrentFile();
        if(isset($curfile)) $this->_btitle .= ( " (" . $curfile . ")" );

        $this->runMainMenu();
    }
    // }}}
    // runMainMenu() {{{
    /**
     * @since 0.1
     */
    public function runMainMenu()
    {
        $opts = array(
            '--backtitle', $this->_btitle,
            '--title', "Main menu"
        );
        $text = "Select configuration step";
        $items = array( 
            'APACHE' => 'Settings related to apache',
            'SYSLOG' => 'Settings related to syslog',
            'ACCEPT' => 'Accept and save changes'
        );
        $cbs = array( // callbacks
            'APACHE' => array($this, 'onApache'),
            'SYSLOG' => array($this, 'onSyslog'),
            'ACCEPT' => array($this, 'onAccept')
        );
        $menu = new WHA_DialogMenu($text, 15, 60, 12, $items, $opts, $cbs);
        $menu->setReturnTag('ACCEPT');
        $menu->run();

        $menu->getExitCode();
    }
    // }}}
    // onApache() {{{
    /**
     * @since 0.1
     */
    public function onApache($caller) {
        $opts = array(
            '--backtitle', $this->_btitle,
            '--title', "Apache configuration menu"
        );
        $text = "Select configuration step";
        $items = array( 
            'PACKAGE' => 'Apache package name',
            'CONFDIR' => 'Apache configuration directory',
            'MODDIR'  => 'Apache modules directory',
            'RETURN'  => 'Return to main menu'
        );
        $cbs = array( // callbacks
            'PACKAGE' => array($this, 'onApachePkgname'),
            'CONFDIR' => array($this, 'onApacheConfdir')
        );
        $menu = new WHA_DialogMenu($text, 15, 60, 12, $items, $opts, $cbs);
        $menu->setReturnTag('RETURN');
        $menu->run();
    }
    // }}}
    // onSyslog() {{{
    /**
     * @since 0.1
     */
    public function onSyslog($caller) {
        $opts = array(
            '--backtitle', $this->_btitle,
            '--title', "Syslog configuration menu"
        );
        $text = "Select configuration step";
        $items = array( 
            'PACKAGE' => 'System logger package name',
            'CONFDIR' => 'System logger configuration directory',
            'RETURN'  => 'Return to main menu'
        );
        $cbs = array( // callbacks
        );
        $menu = new WHA_DialogMenu($text, 15, 60, 12, $items, $opts, $cbs);
        $menu->setReturnTag('RETURN');
        $menu->run();
    }
    // }}}
    // onAccept() {{{
    /**
     * @since 0.1
     */
    public function onAccept($caller) {
        $file = $this->_setup->getCurrentFile();
        if(file_exists($file)) {
            $opts = array(
                '--backtitle', $this->_btitle,
                '--title', "Accept configuration menu",
                '--no-label', "No, go back"
            );
            $text = "You're about to override file '$file'. Continue?";
            $yesno = new WHA_DialogYesno($text, 7, 60, $opts);
            $yesno->run();
            if($yesno-> getExitCode() == DIALOG_OK) {
                $ok = $this->_setup->saveIniFile(); // save current ini file
                if($ok !== true) {
                    $opts2 = $opts;
                    $opts2[4] = "Error";
                    $msgbox = new WHA_DialogMsgbox($ok->getMessage(), 7, 60, 
                                                   $opts2);
                    // Go back to main menu
                    $caller->resume();
                    return;
                }
            } else {
                // User wanted to go back
                $caller->resume();
            }
        }
    }
    // }}}
    // onApachePkgname() {{{
    /**
     * @since 0.1
     */
    public function onApachePkgname($caller) {
        $item = $this->_setup->searchItem(array('apache','pkgname'));
        if($item === false) {
            // TODO: what to do here?. Raise error, or create value?
            throw Exception("That's over!");
        }
        $value = $item->getContent();

        $opts = array(  '--backtitle', $this->_btitle, 
                        '--title', "Apache package name",
                        '--extra-button', '--extra-label', "Guess");
        $text = "Enter the name of your installed apache package";
        $inputbox = new WHA_DialogInputbox($text, 10, 50, $opts);
        $cbs = array(DIALOG_EXTRA => array($this, 'onApachePkgnameGuess'));
        $inputbox->setBtnCallbacks($cbs);
        $inputbox->setValue($value);
        $inputbox->run();
        if($inputbox->getExitCode() == DIALOG_OK)
            $item->setContent($inputbox->getValue());
    }
    // }}}
    // onApacheConfdir() {{{
    /**
     * @since 0.1
     */
    public function onApacheConfdir($caller) {
        $item = $this->_setup->searchItem(array('apache','confdir'));
        if(!isset($item)) {
            // TODO: what to do here?. Raise error, or create value?
            throw Exception("That's over!");
        }
        $fpath = $item->getContent();

        $opts = array(  '--backtitle', $this->_btitle, 
                        '--title', "Apache config directory",
                        '--extra-button', '--extra-label', "Guess");
        $inputbox = new WHA_DialogDselect($fpath, 10, 50, $opts);
        $cbs = array(DIALOG_EXTRA => array($this, 'onApacheConfdirGuess'));
        $inputbox->setBtnCallbacks($cbs);
        $inputbox->setValue($fpath);
        $inputbox->run();
        if($inputbox->getExitCode() == DIALOG_OK)
            $item->setContent($inputbox->getValue());
    }
    // }}}
    // onApachePkgnameGuess() {{{
    /**
     * @since 0.1
     */
    public function onApachePkgnameGuess($caller) {
        $apaches = $this->_setup->findInstalledApaches($err);
        $opts = array( '--backtitle', $this->_btitle, '--title', "");
        if($apaches === false) {
            $opts[3] = "Error";
            if(is_string($err) && strlen($err) > 0) {
                $msgbox = new WHA_DialogMsgbox($err, 7, 60, $opts);
            } else {
                $msgbox = new WHA_DialogMsgbox("an error occurred",7,60,$opts);
            }
            $msgbox->run();
        } 
        $this->confirmGuessResult($caller, $apaches, "Apache installation"); 
        $caller->resume();
    }
    // }}}
    // onApacheConfdirGuess() {{{
    /**
     * @since 0.1
     */
    public function onApacheConfdirGuess($caller) {
        $opts = array( '--backtitle', $this->_btitle, '--title', "");
        $caller->resume();
        $apache = $this->_setup->searchItemContent(array('apache','pkgname'));
        $dirs = array();
        if(!$apache || strlen($apache) == 0) {
            $opts[3] = "Information";
            $text = "Apache package name is not set. You may wish to define ".
                    "it APACHE | PKGNAME.";
            $msgbox = new WHA_DialogMsgbox($text,7,60,$opts);
            $msgbox->run();
        } else {
            $files = wha_pkg_list_files($apache, $err);
            if($files === false) {
                $opts[3] = "Error";
                if(is_string($err) && strlen($err) > 0) {
                    $msgbox = new WHA_DialogMsgbox($err, 7, 60, $opts);
                } else {
                    $msgbox = new WHA_DialogMsgbox("an error occurred",7,60,$opts);
                }
                $msgbox->run();
            } else {
                $re = '#[\\/](apache|httpd)[0-9]{0,2}\\.conf$#';
                $files = array_filter($files, function ($f) use($re) {
                    return preg_match($re, $f);
                });
                foreach($files as $file) {
                    if(is_file($file))
                        $dirs[] = dirname($file);
                }
            }
        }

        // look in some standard places
        $sysconfdirs = array( '/etc', '/usr/local/etc' );
        $subdirs = array( 'apache*', 'httpd*' );
        foreach($sysconfdirs as $sdir) {
            foreach ($subdirs as $subdir) {
                $ds = DIRECTORY_SEPARATOR;
                $glob = glob(implode($ds,array($sdir,$subdir)), GLOB_ONLYDIR);
                // ignore errors and go ahead, its not mission critical 
                if(is_array($glob) && count($glob) > 0) {
                    $dirs = array_merge($dirs, $glob);
                }
            }
        }
        $this->confirmGuessResult($caller, $dirs, "Apache config dir");

    }
    // }}}
    // configmGuessResult() {{{
    /**
     * Confirm result of some guesses. 
     *
     * @param WHA_Inputbox object which is receiving th evalue
     * @param array array of values discovered
     * @param string name of the parameter that was guessed (e.g "config dir")
     * @since 0.1
     */
    public function confirmGuessResult($receiver, $array, $what) {
        $opts = array( '--backtitle', $this->_btitle, '--title', "");
        if (is_array($array)) {
            $array = array_unique($array);
            $opts[3] = "$what(s) found";
            if (count($array) > 1) {
                $items = array_fill_keys($array, array("",0));
                $text = "I guess, one of these is your $what. Chose one.";
                $radiolist = new WHA_DialogRadiolist($text, 12, 50, 10, $items, 
                                                     $opts);
                $radiolist->run();
                if($radiolist->getExitCode() == DIALOG_OK) {
                    $receiver->setValue($radiolist->selection);
                }
                return;
            } elseif (count($array) == 1) {
                $text = "I guess your $what is '$array[0]'. Accept?";
                $yesno = new WHA_DialogYesno($text, 7, 60, $opts);
                $yesno->run();
                if($yesno->getExitCode() == DIALOG_OK)
                    $receiver->setValue($array[0]);
                return;
            }
        }
        $opts[3] = "Information";
        $text = "$what not found.";
        $msgbox = new WHA_DialogMsgbox($text, 6, 50, $opts);
        $msgbox->run();
    }
    // }}}
};

// TODO: Uncomment followint to automatically register this command
 WHA_CliCmdSetup::registerThisCmd();

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
