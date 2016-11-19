CREATING NEW CLI COMMAND
------------------------

Step one
^^^^^^^^

Copy template to new file and perform some substitutions::

    ./newcmd.sh CmdName

Step two
^^^^^^^^

Add new entry::

    php/WHA/CliCmds/${CN1}.php

to variable ``php_sources`` in file ``../../../php-sources.mk``.

Step three
^^^^^^^^^^

Implement ``WHA_CliCmd${CN1}::execute()``.


DISABLING CERTAIN COMMANDS
--------------------------

Comment out the line which registers the command. For example::

    WHA_CliCmdXxx::registerThisCmd();

should become::

    // WHA_CliCmdXxx::registerThisCmd();

