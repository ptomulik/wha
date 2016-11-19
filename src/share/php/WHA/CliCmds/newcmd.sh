#! /bin/sh

# Copyright (c) 2013 by Pawel Tomulik <ptomulik@meil.pw.edu.pl>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE

set -e
if [ $# -lt 1 ]; then
  echo './newcmd.sh CmdName' >&2;
  exit 1;
fi


camel=$1;
lower=`echo $camel | tr '[:upper:]' '[:lower:]'`;
file="${camel}.php"

if [ -e "$file" ]; then
  echo "File ${file}.php already exists, won't override!" >&2;
  exit 1;
fi


cp Template.php ${file} && sed -e "s/xxx/${lower}/g" -e "s/Xxx/${camel}/g" \
    -e 's/^\/\/::\/\///' ${file} -i
