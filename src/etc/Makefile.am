include $(top_srcdir)/src/sedvars.mk


# nobase_dist_sysconf_DATA =
nobase_nodist_sysconf_DATA = wha/tools.ini wha/wha.ini

EXTRA_DIST = $(srcdir)/wha/tools.ini.in \
						 $(srcdir)/wha/wha.ini.in

wha/tools.ini: wha/tools.ini.in _sedvars.sed
	( $(TEST) -e ./wha || $(MKDIR) ./wha ) && \
	  $(SED) -f _sedvars.sed $(srcdir)/wha/tools.ini.in > ./wha/tools.ini

wha/wha.ini: wha/wha.ini.in _sedvars.sed
	( $(TEST) -e ./wha || $(MKDIR) ./wha ) && \
	  $(SED) -f _sedvars.sed $(srcdir)/wha/wha.ini.in > ./wha/wha.ini

local_cleanfiles = wha/tools.ini wha/wha.ini

CLEANFILES = $(sedvars_mk_cleanfiles) $(local_cleanfiles)
SUFFIXES = $(sedvars_mk_suffixes)
