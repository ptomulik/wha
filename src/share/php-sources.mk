php_sources = $(srcdir)/php/WHA.php \
              $(srcdir)/php/WHA/Cli.php \
              $(srcdir)/php/WHA/CliCmd.php \
              $(srcdir)/php/WHA/CliCmdFactory.php \
              $(srcdir)/php/WHA/CliCmds.php \
              $(srcdir)/php/WHA/CliCmds/Help.php \
              $(srcdir)/php/WHA/CliCmds/Setup.php \
              $(srcdir)/php/WHA/CliCmds/Test.php \
              $(srcdir)/php/WHA/CliCmds/Version.php \
              $(srcdir)/php/WHA/CliMain.php \
              $(srcdir)/php/WHA/Cmd.php \
							$(srcdir)/php/WHA/ConfEdit.php \
              $(srcdir)/php/WHA/Dialog/Dselect.php \
              $(srcdir)/php/WHA/Dialog/Fselect.php \
              $(srcdir)/php/WHA/Dialog/Functions.php \
              $(srcdir)/php/WHA/Dialog/Inputbox.php \
              $(srcdir)/php/WHA/Dialog/Menu.php \
              $(srcdir)/php/WHA/Dialog/Msgbox.php \
              $(srcdir)/php/WHA/Dialog/Radiolist.php \
              $(srcdir)/php/WHA/Dialog/Widget.php \
              $(srcdir)/php/WHA/Dialog/Yesno.php \
              $(srcdir)/php/WHA/Misc.php \
              $(srcdir)/php/WHA/PkgQueryTool.php \
              $(srcdir)/php/WHA/Tools.php \
              $(srcdir)/php/WHA/commands.php \
              $(srcdir)/php/WHA/commands/help.php \
              $(srcdir)/php/WHA/commands/setup.php \
              $(srcdir)/php/WHA/commands/setup/dialog.php \
              $(srcdir)/php/WHA/commands/version.php \
              $(srcdir)/php/WHA/file_annotations.php \
              $(srcdir)/php/WHA/getopts.php \
              $(srcdir)/php/WHA/main.php \
              $(srcdir)/php/WHA/object.php \
              $(srcdir)/php/WHA/shell_config_file_annotations.php \
              $(srcdir)/php/WHA/wha.php

php_in_sources = $(srcdir)/php/WHA/version.php.in
php_generated	= php/WHA/version.php
