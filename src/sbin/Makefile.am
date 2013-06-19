include $(top_srcdir)/src/sedvars.mk

sbin_SCRIPTS = wha
EXTRA_DIST = wha.in

wha: wha.in _sedvars.sed 
	$(SED) -f _sedvars.sed $(srcdir)/wha.in > wha

local_cleanfiles = wha
CLEANFILES = $(sedvars_mk_cleanfiles) $(local_cleanfiles)
SUFFIXES = $(sedvars_mk_suffixes)
