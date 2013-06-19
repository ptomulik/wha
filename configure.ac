AC_INIT([wha], [0.1], [ptomulik@meil.pw.edu.pl])
AM_INIT_AUTOMAKE([])

dnl ==================== THIS MUST BE AT THE FIRST PLACE ====================
AC_PROG_TEST
dnl ==================== THIS MUST BE AT THE FIRST PLACE ====================

dnl =========================================================================
dnl required programs/commands
dnl =========================================================================
AC_PROG_TRUE
AC_PROG_FALSE
AC_PROG_AWK
AC_PROG_CMP
AC_PROG_CUT
AC_PROG_DIALOG
AC_PROG_FIND
AC_PROG_GREP
AC_PROG_MD5
AC_PROG_MKDIR
AC_PROG_MKTEMP
AC_PROG_PHP
AC_PROG_PRINTF
AC_PROG_SED
AC_PROG_SORT
AC_PROG_TOUCH
AC_PROG_TR
AC_PROG_UNIQ
AC_PROG_WC

dnl =========================================================================
dnl optional programs
dnl =========================================================================
AC_PATH_PROG([PKG_INFO], [pkg_info])
AC_PATH_PROG([DPKG_QUERY], [dpkg-query])

dnl =========================================================================
dnl --with-xxx arguments
dnl =========================================================================
AC_ARG_WITH([apigen],
  [AS_HELP_STRING([--with-apigen],
    [use apigen to generate API documentation @<:@default=check@:>@])],
  [],
  [with_apigen=check])

AS_IF([${TEST} "x$with_apigen" != xno],[AC_PATH_PROG([APIGEN], [apigen])])

AS_IF([${TEST} "x$APIGEN" == x], 
  [AS_IF([${TEST} "x$with_apigen" != xno], 
     [AS_IF([${TEST} "x$with_apigen" != xcheck],
        [AC_MSG_FAILURE([--with-apigen was given, but test for apigen failed])])])])


dnl =========================================================================
dnl substitutions
dnl =========================================================================
AC_SUBST([WHA_PHP_INCLUDE_PATH], [${datadir}/php])

dnl =========================================================================
dnl automake conditionals
dnl =========================================================================
AM_CONDITIONAL([GENERATE_APIDOC], [test x${APIGEN} != x])

dnl =========================================================================
dnl runtime behavior
dnl =========================================================================
AC_CHECK_PEAR_CONFIG

dnl =========================================================================
dnl generate config files
dnl =========================================================================
AC_CONFIG_FILES([
  Makefile
  src/Makefile
  src/etc/Makefile
  src/share/Makefile
  src/sbin/Makefile
  ])

AC_OUTPUT

dnl vim: set syntax=m4 nospell:
