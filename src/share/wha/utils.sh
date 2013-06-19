#! /bin/sh

#############################################################################
# share/wha/utils.sh
#
# Various utility functions. 
#############################################################################

set -e

#############################################################################
# ss str
#   Squeeze spaces in string.
#############################################################################
ss() { echo "$1" | tr -s '[:space:]' ' '; }

#############################################################################
# wha_pkg_installed pkg
#
#   Check, if the given package is installed
#
# return
#   0   installed
# 255   not installed
# 127   no usable package querying tool configured
#   *   error when querying package name
#############################################################################
wha_package_installed()
{
  local pkg=$1;
  local status=0;
  if [ ! -z ${PKG_INFO} ] && [ -x ${PKG_INFO} ] ; then
    # FreeBSD
    pkg2=`${PKG_INFO} -E "$pkg"` || status=$?;
  elif [ ! -z ${DPKG_QUERY} ] && [ -x  ${DPKG_QUERY} ]; then
    # Debian/Ubuntu
    pkg2=`${DPKG_QUERY} -f='${binary:Package} ${Status}\n' -W "${pkg}" \
      2> /dev/null | ${AWK} -F' +' '$4 == "installed" {print $1}'` \
      || status=$?;
  else
    return 127;
  fi
  return ${status};
}

#############################################################################
# wha_guess_apache_pkgname
#
#   Try to guess the name of installed apache server package.
#
# return
#   0   success
#   1   error when querying package name
# 127   no usable package querying tool configured
#############################################################################
wha_guess_apache_pkgname()
{
  if [ ! -z ${PKG_INFO} ] && [ -x ${PKG_INFO} ] ; then
    # FreeBSD
    ${PKG_INFO} -E -x '^apache[0-9]\{0,2\}-.[0-9_\.-]\{1,\}$' 
    return $?
  elif [ ! -z ${DPKG_QUERY} ] && [ -x  ${DPKG_QUERY} ]; then
    # Debian/Ubuntu
    ${DPKG_QUERY} -f='${binary:Package} ${Status}\n' -W 'apache*' \
      | ${AWK} -F' +' '$4 == "installed" && $1 ~ /^apache[0-9]{0,2}$/ {print $1}'
    return $?
  else
    return 127;
  fi
}

#############################################################################
# wha_guess_apache_conf_dir_from_pkg pkgname
#
#   Try to guess the directory name, where apache stores its configuration.
#   Use package manager
#
# Arguments:
#
#   pkgname   name of the apache package as installed by system package
#             manager, if not given, the package manager will not be used,
#             and nothing will be returned.
# Return
#   0   success
#   1   error, e.g. package is not installed, or pkgname is empty
# 127   no usable package querying tool configured
#############################################################################
wha_guess_apache_conf_dir_from_pkg()
{
  local pkgname=$1

  # Use package manager in the first place
  if [ ! -z ${pkgname} ]; then
    if [ ! -z ${PKG_INFO} ] && [ -x ${PKG_INFO} ] ; then
      local dirsuffix=`echo ${pkgname} | ${AWK} -F'-' '{print $1}'`
      local regex="\(\/etc\/${dirsuffix}\)\(\/.*\)\{0,1\}\$";
      # FreeBSD
      ${PKG_INFO} -L "${pkgname}" | ${GREP} "${regex}" \
        | ${SED} -e "s/${regex}/\1/" | ${UNIQ} 
      return $?
    elif [ ! -z ${DPKG_QUERY} ] && [ -x  ${DPKG_QUERY} ]; then
      # Debian/Ubuntu
      local dirsuffix=${pkgname}
      local regex="\(\/etc\/${dirsuffix}\)\(\/.*\)\?\$";
      ${DPKG_QUERY} -L ${pkgname}  | ${GREP} "${regex}" \
        | ${SED} -e "s/${regex}/\1/" | ${UNIQ} 
      return $?
    else
      return 127;
    fi
  else
    return 1
  fi
}

#############################################################################
#
#############################################################################
wha_guess_apache_conf_dirs_by_lookup()
{
  # Look in usual locations (note, no whitespaces in paths)
  ls -d /etc/httpd 2> /dev/null  || true;
  ls -d /etc/httpd[0-9] 2> /dev/null || true;
  ls -d /etc/httpd[0-9][0-9] 2> /dev/null || true;
  ls -d /etc/apache 2> /dev/null || true;
  ls -d /etc/apache[0-9] 2> /dev/null || true;
  ls -d /etc/apache[0-9][0-9] 2> /dev/null || true;
  ls -d /usr/local/etc/httpd 2> /dev/null || true;
  ls -d /usr/local/etc/httpd[0-9] 2> /dev/null || true;
  ls -d /usr/local/etc/httpd[0-9][0-9] 2> /dev/null || true;
  ls -d /usr/local/etc/apache 2> /dev/null || true;
  ls -d /usr/local/etc/apache[0-9] 2> /dev/null || true;
  ls -d /usr/local/etc/apache[0-9][0-9] 2> /dev/null || true;
}

#############################################################################
# wha_guess_apache_conf_dirs [pkgname]
#
#############################################################################
wha_guess_apache_conf_dirs()
{
  local dir1=;
  local dirs2=;
  local status=0;

  [ -z $1 ] || { dir1=`wha_guess_apache_conf_dir_from_pkg "$1"` || status=$?; }
  dirs2=`wha_guess_apache_conf_dirs_by_lookup`

  local dirs=;
  local sep=;
  for d in "${dir1}" ${dirs2}; do
    [ -d "${d}" ] && dirs=`printf "%s%s%s" "${dirs}" "${sep}" "${d}"`;
    sep=" ";
  done
 
  echo $dirs | ${UNIQ}

  return 0
}

#############################################################################
# wha_get_value name
#############################################################################
wha_get_value()
{
  local val=;
  local usr=;
  usr=$(echo $(eval echo "\${WHA_USR_$1}"))
  [ -z "${usr}" ] || val=$(echo $(eval echo "\${WHA_USR_$1}"))
  [ -z "${val}" ] && val=$(echo $(eval echo "\${WHA_$1}"))
  echo "${val}"
}

#############################################################################
# wha_get_apache_pkgname:
#
#   Print current value of apache package name used by configuration
#   functions/dialogs.
#
#############################################################################
wha_get_apache_pkgname() 
{ 
  wha_get_value APACHE_PKGNAME;
}

#############################################################################
# wha_get_apache_conf_dir:
#
#   Print current value of apache config directory path used by configuration
#   functions/dialogs. 
#
#############################################################################
wha_get_apache_conf_dir()
{ 
  wha_get_value APACHE_CONF_DIR;
}
