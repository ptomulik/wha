##############################################################################
# wha_sed_directory_variables:
#
# List of GNU directory variables to be interpolated in scripts.
##############################################################################
wha_sed_directory_variables = \
  s:[@]prefix[@]:$(prefix):g \
  s:[@]exec_prefix[@]:$(exec_prefix):g \
  s:[@]bindir[@]:$(bindir):g \
  s:[@]sbindir[@]:$(sbindir):g \
  s:[@]libexecdir[@]:$(libexecdir):g \
  s:[@]datarootdir[@]:$(datarootdir):g \
  s:[@]datadir[@]:$(datadir):g \
  s:[@]sysconfdir[@]:$(sysconfdir):g \
  s:[@]sharedstatedir[@]:$(sharedstatedir):g \
  s:[@]localstatedir[@]:$(localstatedir):g \
  s:[@]includedir[@]:$(includedir):g \
  s:[@]oldincludedir[@]:$(oldincludedir):g \
  s:[@]docdir[@]:$(docdir):g \
  s:[@]infodir[@]:$(infodir):g \
  s:[@]htmldir[@]:$(htmldir):g \
  s:[@]dvidir[@]:$(dvidir):g \
  s:[@]pdfdir[@]:$(pdfdir):g \
  s:[@]psdir[@]:$(psdir):g \
  s:[@]libdir[@]:$(libdir):g \
  s:[@]lispdir[@]:$(lispdir):g \
  s:[@]localedir[@]:$(localedir):g \
  s:[@]mandir[@]:$(mandir):g \
  s:[@]pkgdatadir[@]:$(pkgdatadir):g \
  s:[@]pkgincludedir[@]:$(pkgincludedir):g \
  s:[@]pkglibdir[@]:$(pkglibdir):g \
  s:[@]pkglibexecdir[@]:$(pkglibexecdir):g

# variables from configure_ac
wha_sed_configure_ac_variables = \
	s:[@]PACKAGE[@]:$(PACKAGE):g \
	s:[@]VERSION[@]:$(VERSION):g \
	s:[@]AWK[@]:$(AWK):g \
	s:[@]CUT[@]:$(CUT):g \
	s:[@]DIALOG[@]:$(DIALOG):g \
	s:[@]DPKG[@]:$(DPKG):g \
	s:[@]DPKG_QUERY[@]:$(DPKG_QUERY):g \
	s:[@]GREP[@]:$(GREP):g \
	s:[@]MKTEMP[@]:$(MKTEMP):g \
	s:[@]PHP[@]:$(PHP):g \
	s:[@]PKG_INFO[@]:$(PKG_INFO):g \
	s:[@]SED[@]:$(SED):g \
	s:[@]TR[@]:$(TR):g \
	s:[@]UNIQ[@]:$(UNIQ):g \
	s:[@]WC[@]:$(WC):g \
	s:[@]WHA_PHP_INCLUDE_PATH[@]:$(WHA_PHP_INCLUDE_PATH):g


##############################################################################
# wha_sed_variables:
#
# List of automake variables to be sedd in certain scripts.
##############################################################################
wha_sed_variables = \
$(wha_sed_directory_variables) \
$(wha_sed_configure_ac_variables)


# use separate "_sedvars.sed" in each subdirectory, because each subdir's
# Makefile may have different environment and in general shared file can
# break parallel builds.
_sedvars.tmp::
	@echo 'echo $$(wha_sed_variables) > _sedvars.tmp'
	@echo $(wha_sed_variables) | $(SED) -e 's/^ \+//' | $(TR) -s ' ' '\n' > $@

# create _sedvars.sed if it doesn't exist or its content is outdated
_sedvars.sed: _sedvars.tmp
	( test -e _sedvars.sed && cmp -s _sedvars.tmp _sedvars.sed ) \
		|| cp _sedvars.tmp _sedvars.sed

sedvars_mk_cleanfiles = _sedvars.tmp _sedvars.sed

# vim: set syntax=automake:
