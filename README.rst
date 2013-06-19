WHA
===

Welcome to **wha** package. This is a simple PHP scripts that may be used to
maintain web server with virtual hosts and multiple webmasters' system
accounts. The name is abbreviation for Web Hosting Administrator.

Note: this is in initial state, nothing works yet. It's placed here just as a 
backup and may be removed at any time.

REQUIREMENTS
============

TO COMPILE AND INSTAL SCRIPTS
-----------------------------
  
  - `GNU make`_ or `BSD make`_
  - sh shell with some standard tools.

TO RUN THE INSTALLED SCRIPTS
----------------------------
 
  - `PHP 5`_,
  - `PEAR Config`_::

        pear install Config

  - unix dialog_ pogram,

TO GENERATE API DOCUMENTATION
-----------------------------

  - apigen_::

        pear config-set auto_discover 1
        pear install pear.apigen.org/apigen

LICENSE
=======

Copyright (c) 20013 Paweł Tomulik <ptomulik@meil.pw.edu.pl>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE

.. _dialog: http://invisible-island.net/dialog/
.. _apigen: http://apigen.org/
.. _GNU make: http://www.gnu.org/software/make/
.. _BSD make: http://www.freebsd.org/doc/en/books/developers-handbook/tools-make.html
.. _PHP 5: http://www.php.net/
.. _PEAR Config: http://pear.php.net/package/Config
