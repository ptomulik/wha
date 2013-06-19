#! /bin/sh

set -e

#############################################################################
# share/wha/dialogs.sh
#
# Library of dialogs for webhostadm scripts.
#############################################################################

. ${WHA_PKGDATA_DIR}/utils.sh


#############################################################################
# wha_dlg_inputbox [-b btitle] [-h help] [-x extra] title text height width
#                  [value]
#
#   Display inputbox to ask for simple text. 
#
# NOTE: 
#   file descriptor 8 is used for reading
#   file descriptor 9 is used for writing
#   result should be read from file descriptor 8
#
# EXAMPLE:
#
#   exec 8< tmpfile
#   exec 9> tmpfile
#   # ...
#   wha_dlg_inputbox "Input box" "Hello, welcome to input box" 8 20 "default" 
#   answer=`cat <&8`
#   # ...
#   exec 8< &-
#   exec 9> &-
#
# Options:
#   -b btitle back title,
#   -h help   help message to be shown, when user presses 'Help' button.
#             this parameter must have the form "message text H W",
#             where H is the height of help window and W is the width,
#             for example "this is a help for function foo 5 20",
#   -x extra  a string in form "caption:function", where the caption is used
#             for extra button and function is a shell function name to be
#             invoked with current value when the extra button is pressed,
#
# Arguments:
#   title     title of the inputbox window,
#   text      a text message to be shown in the inputbox window,
#   height    height of the inputbox widget
#   width     width of the inputbox widget
#   value     initial value in of the inputbox
#
#############################################################################
wha_dlg_inputbox()
{
  local btitle=;
  local hlp=;
  local extra=;
  local check=;

  local f_btitle=;
  local f_hlp=;
  local f_extra=;
  local extra_label=;
  local extra_funct=;

  local optname=;

  local n=;
  local ht=;
  local hh=;
  local hw=;
  while getopts b:h:x: optname; do
    case ${optname} in
      b)  btitle=`ss "${OPTARG}"`;
          f_btitle='--backtitle'
          ;;
      h)
          n=`ss "${OPTARG}" | ${WC} -w`;
          ht=`ss "${OPTARG}" | ${CUT} -d ' ' -f-$(($n-2))`;
          hh=`ss "${OPTARG}" | ${CUT} -d ' ' -f$(($n-1))`;
          hw=`ss "${OPTARG}" | ${CUT} -d ' ' -f$n`;
          f_hlp='--help-button';
          ;;
      x)  n=`echo "${OPTARG}" | ${AWK} -F':' '{print NF}'`;
          extra_label=`echo "${OPTARG}" | ${CUT} -d':' -f 1`;
          extra_funct=`echo "${OPTARG}" | ${CUT} -d':' -f 2`;
          f_extra='--extra-button --extra-label';
          ;;
      ?)  exit 1;
          ;;
      *)  echo 'wha_dlg_inputbox: internal error (unhandled option)';
          exit 1;
          ;;
    esac
  done

  shift $(($OPTIND - 1))

  if [ $# -lt 4 ]; then
    echo "wha_dlg_inputbox: at least 4 arguments required, but only $# provided" >&2;
    exit 1;
  fi

  local title="$1";
  local msg="$2";
  local h="$3";
  local w="$4";
  local val=;

  [ $# -ge 5 ] && val=$5;


  # Construct command-line options/arguments to dialog
  local cl=;
  cl=`printf '%s "%s"' "--title" "${title}"`
  [ -z "${f_btitle}" ] \
    || cl=`printf '%s %s "%s"' "${cl}" "${f_btitle}" "${btitle}"`
  [ -z "${f_extra}" ] \
    || cl=`printf '%s %s "%s"' "${cl}" "${f_extra}" "${extra_label}"`
  [ -z "${f_hlp}" ] \
    || cl=`printf '%s %s' "${cl}" "${f_hlp}"`
  cl=`printf '%s --inputbox --output-fd 9 "%s" %d %d' "${cl}" "${msg}" \
      "${h}" "${w}"`

  [ -z "${f_hlp}" ] \
    || hcl=`printf '%s "Help - %s" --msgbox "%s" "%d" "%d"' "--title" \
                   "${title}" "${ht}" "${hh}" "${hw}"`;

  local done=0;
  local status=;

  # open file descriptor for dialog
  while [ ${done} -eq 0 ] ; do
    # show dialog box
    status=0
    eval ${DIALOG} ${cl} "${val}" || status=$?
    val=`cat <&8`;

    case ${status} in 
      0)  echo "${val}" >&9; done=1;;
      2)  eval ${DIALOG} ${hcl} ;;
      3)  local status2=0;
          # FIXME: do something with status2?
          ${extra_funct} "${val}" || status2=$?;
          val=`cat <&8`
          ;;
      *) done=1 ;;
    esac
  done

  return ${status}
}

#############################################################################
# wha_dlg_dselect [-b btitle] [-h help] [-x extra] title path height width
#
#   Display directory selection window to ask for directory name. 
#
# NOTE: 
#   file descriptor 8 is used for reading
#   file descriptor 9 is used for writing
#   result should be read from file descriptor 8
#
# EXAMPLE:
#
#   exec 8< tmpfile
#   exec 9> tmpfile
#   # ...
#   wha_dlg_dselect "Select direcotory" "/" 15 20 
#   answer=`cat <&8`
#   # ...
#   exec 8< &-
#   exec 9> &-
#
# Options:
#   -b btitle back title,
#   -h help   help message to be shown, when user presses 'Help' button.
#             this parameter must have the form "message text H W",
#             where H is the height of help window and W is the width,
#             for example "this is a help for function foo 5 20",
#   -x extra  a string in form "caption:function", where the caption is used
#             for extra button and function is a shell function name to be
#             invoked with current value when the extra button is pressed,
#
# Arguments:
#   title     title of the dselect window,
#   path      initial path,
#   height    height of the dselect widget
#   width     width of the dselect widget
#
#############################################################################
wha_dlg_dselect()
{
  local btitle=;
  local hlp=;
  local extra=;
  local check=;

  local f_btitle=;
  local f_hlp=;
  local f_extra=;
  local extra_label=;
  local extra_funct=;

  local optname=;

  local n=;
  local ht=;
  local hh=;
  local hw=;
  while getopts b:h:x: optname; do
    case ${optname} in
      b)  btitle=`ss "${OPTARG}"`;
          f_btitle='--backtitle'
          ;;
      h)
          n=`ss "${OPTARG}" | ${WC} -w`;
          ht=`ss "${OPTARG}" | ${CUT} -d ' ' -f-$(($n-2))`;
          hh=`ss "${OPTARG}" | ${CUT} -d ' ' -f$(($n-1))`;
          hw=`ss "${OPTARG}" | ${CUT} -d ' ' -f$n`;
          f_hlp='--help-button';
          ;;
      x)  n=`echo "${OPTARG}" | ${AWK} -F':' '{print NF}'`;
          extra_label=`echo "${OPTARG}" | ${CUT} -d':' -f 1`;
          extra_funct=`echo "${OPTARG}" | ${CUT} -d':' -f 2`;
          f_extra='--extra-button --extra-label';
          ;;
      ?)  exit 1;
          ;;
      *)  echo 'wha_dlg_dselect: internal error (unhandled option)';
          exit 1;
          ;;
    esac
  done

  shift $(($OPTIND - 1))

  if [ $# -lt 4 ]; then
    echo "wha_dlg_dselect: 4 arguments required, but only $# provided" >&2;
    exit 1;
  fi

  local title="$1";
  local val="$2";
  local h="$3";
  local w="$4";


  # Construct command-line options/arguments to dialog
  local cl=;
  cl=`printf '%s "%s"' "--title" "${title}"`
  [ -z "${f_btitle}" ] \
    || cl=`printf '%s %s "%s"' "${cl}" "${f_btitle}" "${btitle}"`
  [ -z "${f_extra}" ] \
    || cl=`printf '%s %s "%s"' "${cl}" "${f_extra}" "${extra_label}"`
  [ -z "${f_hlp}" ] \
    || cl=`printf '%s %s' "${cl}" "${f_hlp}"`
  cl=`printf '%s --dselect --output-fd 9' "${cl}"`

  [ -z "${f_hlp}" ] \
    || hcl=`printf '%s "Help - %s" --msgbox "%s" "%d" "%d"' "--title" \
                   "${title}" "${ht}" "${hh}" "${hw}"`;

  local done=0;
  local status=;

  # open file descriptor for dialog
  while [ ${done} -eq 0 ] ; do
    # show dialog box
    status=0
    eval ${DIALOG} ${cl} "\"${val}\"" ${h} ${w} || status=$?
    val=`cat <&8`;

    case ${status} in 
      0)  echo "${val}" >&9; done=1;;
      2)  eval ${DIALOG} ${hcl} ;;
      3)  local status2=0;
          # FIXME: do something with status2?
          ${extra_funct} "${val}" || status2=$?;
          val=`cat <&8`
          ;;
      *) done=1 ;;
    esac
  done


  return ${status}
}

#############################################################################
# wha_dlg_reconfigure_main
#
#   Main menu for webhostadm-reconfigure with configuration steps.
#
# NOTE: 
#   file descriptor 8 is used for reading
#   file descriptor 9 is used for writing
#
# Requires:
#   WHA_APACHE_PKGNAME
#   WHA_APACHE_CONF_DIR
#
# Sets:
#   WHA_USR_APACHE_PKGNAME
#   WHA_USR_APACHE_CONF_DIR
#############################################################################
wha_dlg_reconfigure_main()
{
  local status;
  local done=0;
  local title="Configuration steps";
  local btitle="Webhostadm Main Configuration";
  local txt="Select configuratio step";

  local answer="";

  while [ ${done} -eq 0 ] ; do
    status=0
    ${DIALOG} --title "${title}" --backtitle "${btitle}" --help-button \
              --default-item "${answer}" --menu --output-fd 9 "${txt}" \
              24 50 20 \
              APACHE  "Apache configuration" \
              LOGS    "Logging and logrotate settings" \
              SITES   "Defaults for websites settings" \
              USERS   "Defaults for user accounts" \
              COMMIT  "Finish and commit configuration" \
      || status=$?
    answer=`cat <&8`
    case ${status} in
      0) 
        case ${answer} in
          'APACHE')
            wha_dlg_reconfigure_apache
            ;;
          'LOGS')
            ;;
          'SITES')
            ;;
          'USERS')
            ;;
          'COMMIT')
            done=1
            ;;
        esac
        ;;
      *) done=1;;
    esac
  done

  return ${status}
}

#############################################################################
# wha_dlg_reconfigure_apache
#
# NOTE: 
#   file descriptor 8 is used for reading
#   file descriptor 9 is used for writing
#
# WHA_USR_APACHE_PKGNAME
#############################################################################
wha_dlg_reconfigure_apache()
{
  local status=0;
  local done=0;
  local title="Configuration steps";
  local btitle="Webhostadm Main Configuration";
  local txt="Select configuratio step";

  local answer="";
  local pkg=;
  local dir=;
  local status2=;

  while [ ${done} -eq 0 ] ; do
    pkg=`wha_get_apache_pkgname`;
    dir=`wha_get_apache_conf_dir`;
    status=0
    ${DIALOG} --title "${title}" --backtitle "${btitle}" --help-button \
               --default-item "${answer}" --menu --output-fd 9 "${txt}" \
              24 70 20 \
              APACHE  "`printf 'Apache package name (%s)' "${pkg}"`" \
              CONFIG  "`printf 'Apache config directory (%s)' "${dir}"`" \
              RETURN  "Return to main menu" \
      || status=$?
    answer=`cat <&8`
    case ${status} in
      0) 
        case ${answer} in
          'APACHE')
            status2=0;

            wha_dlg_apache_pkgname "${pkg}" || status2=$?;

            [ "${status2}" -eq 0 ] && pkg=`cat <&8`
            WHA_USR_APACHE_PKGNAME="${pkg}"
            ;;
          'CONFIG')
            status2=0;

            wha_dlg_apache_conf_dir "${dir}" || status2=$?;

            [ "${status2}" -eq 0 ] && dir=`cat <&8`
            WHA_USR_APACHE_CONF_DIR="${dir}"
            ;;
          'RETURN')
            done=1
            ;;
        esac
        ;;
      *) done=1;;
    esac
  done

  return ${status}
}

#############################################################################
# wha_dlg_guess_apache_pkgname name
#
#   Guess apache package name - widget version
#
# Always writes name (updated, or not) to &9.
#
#############################################################################
wha_dlg_guess_apache_pkgname()
{
  local title="Apache package name (as installed)";
  local btitle="Apache configuration"
  local name=$1;
  local status=0;
  local guess=;
  local msg1=;
  local msg2=;

  guess=`wha_guess_apache_pkgname` || status=$?;

  msg1=`ss "I guess your apache package is: ${guess}. Accept?"`;
  msg2=`ss "I found no usable tool to query packages. Maybe you should \
            configure one in ${WHA_CONF_DIR}/webhostadm/paths.conf?"`
  case  ${status} in
    0)  ${DIALOG} --title "${title}" --backtitle "${btitle}" --yesno \
          "${msg1}" 6 60 || status=$? 
        [ $status -eq 0 ] && name=${guess};
        ;;
  127)  ${DIALOG} --title  "Failure"  --backtitle "${btitle}" --msgbox \
          "${msg2}" 8 50;
        ;;
    *)  ${DIALOG} --title "Error" --backtitle "${btitle}" \
                  --msgbox "An error occurred when querying package name.\
                            Do you have apache installed?" 5 60;   
        ;;                                                              
  esac

  echo ${name} >&9;

  return ${status}
}

#############################################################################
# wha_dlg_guess_apache_conf_dir
#
#   Guess apache configuration directory path - widget version
#
# Always writes name (updated, or not) to &9.
#
#############################################################################
wha_dlg_guess_apache_conf_dir()
{
  local title="Apache configuration directory";
  local btitle="Apache configuration"
  local status=0;
  local guess=;
  loacl msg1=;
  local msg2=;
  local msg3=;
  local pkg=;
  local items=;
  local sep=;
  local i=;

  msg1=`ss "The apache package name is not set, so I can't use package \
            manager for guessing. Will only look for typical locations"`
  msg2=`ss "Select one of the proposed paths from menu."`
  msg3=`ss "Guess failed, no directory found."`

  pkg=`wha_get_apache_pkgname`
  [ -z "${pkg}" ] && ${DIALOG} --title "${title}" --backtitle "${btitle}" \
                      --msgbox "${msg1}" 8 60 ;

  guess=`wha_guess_apache_conf_dirs` 

  if [ ! -z "${guess}" ]; then
    i=1;
    for d in ${guess}; do
      items=`printf "%s%s%s %s" "${items}" "${sep}" "${d}" $i`;
      sep=" "
      i=$((i+1))
    done
    ${DIALOG} --title "${title}" --backtitle "${btitle}" --menu --output-fd 9 \
      "${msg2}" 15 70 10 ${items} || status=$?
    [ ${status} -eq 0 ] || echo "$1" >&9;
    return ${status}
  else
    ${DIALOG} --title "${title}" --backtitle "${btitle}" --msgbox "${msg3}" \
      5 60
    echo "$1" >&9
    return 1;
  fi
}


#############################################################################
# wha_dlg_apache_pkgname pkg
#
#   Ask for the apache package name using wha_dlg_inputbox
#
# NOTE: 
#   file descriptor 8 is used for reading
#   file descriptor 9 is used for writing
#
#############################################################################
wha_dlg_apache_pkgname ()
{
  local title="Apache package name (as installed)";
  local msg="Enter the package name of your installed apache server or \
    press 'Guess' to let me guess it.";
  local hlp="The apache package name is the name of apache package\
    installed using your packaging system. This is usually something \
    like 'apache', 'apache2' (Debian) or 'apache24-2.4.4_1' (FreeBSD). \
    The name you provide here will be used at other configuration steps \
    to query your package manager (pkg_info, dpkg-query, etc.) for \
    apache's characteristics - for example to guess where are the \
    configuration files located.";
  local status=0

  wha_dlg_inputbox -h "${hlp} 15 60" -b "Apache configuration" \
    -x 'Guess:wha_dlg_guess_apache_pkgname' "${title}" "${msg}" 8 70 "$1" \
      || status=$?

  return ${status}
}

#############################################################################
# wha_dlg_apache_conf_dir
#   
#   Ask for the apache configuration directory
#
# Requires:
#   WHA_APACHE_CONF_DIR   original package name (from configuration file),
#
# Sets:
#   WHA_USR_PACHE_CONF_DIR  user's answer
#
# Return:
#   0   user pressed OK
#   1   user pressed cancel
# 255   error in dialog occurred or user pressed ESC (abort)
#############################################################################
wha_dlg_apache_conf_dir()
{
  local title="Apache configuration directory"
  local hlp="Apache configuration directory is where apache keeps its \
             configuration files. Depending on the OS and apache version it \
             may look like '/etc/apache', '/etc/apache2', etc. Check your \
             apache installation for exact information where the files are \
             located, or press 'Guess' button to let me try to guess.";

  local status=0;
  wha_dlg_dselect -h "${hlp} 15 60" -b "Apache configuration" \
    -x "Guess:wha_dlg_guess_apache_conf_dir" "${title}" "$1" 20 60 || status=$?

  return ${status}
}
