include $(top_srcdir)/src/sedvars.mk


nobase_dist_sysconf_DATA = wha/apache.conf.local \
													 wha/wha.conf

nobase_nodist_sysconf_DATA = 	wha/paths.conf \
									 					 	wha/tools.ini \
															wha/wha.ini

EXTRA_DIST = $(srcdir)/wha/paths.conf.in \
						 $(srcdir)/wha/tools.ini.in \
						 $(srcdir)/wha/wha.ini.in

wha/paths.conf: wha/paths.conf.in _sedvars.sed
	( $(TEST) -e ./wha || $(MKDIR) ./wha ) && \
		$(SED) -f _sedvars.sed $(srcdir)/wha/paths.conf.in > ./wha/paths.conf

wha/tools.ini: wha/tools.ini.in _sedvars.sed
	( $(TEST) -e ./wha || $(MKDIR) ./wha ) && \
	  $(SED) -f _sedvars.sed $(srcdir)/wha/tools.ini.in > ./wha/tools.ini

wha/wha.ini: wha/wha.ini.in _sedvars.sed
	( $(TEST) -e ./wha || $(MKDIR) ./wha ) && \
	  $(SED) -f _sedvars.sed $(srcdir)/wha/wha.ini.in > ./wha/wha.ini

local_cleanfiles = wha/paths.conf \
									 wha/tools.ini \
									 wha/wha.ini

CLEANFILES = $(sedvars_mk_cleanfiles) $(local_cleanfiles)
SUFFIXES = $(sedvars_mk_suffixes)
