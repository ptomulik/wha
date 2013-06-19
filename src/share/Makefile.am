include $(top_srcdir)/src/sedvars.mk
include php-sources.mk

nobase_dist_data_DATA = $(php_sources)
nobase_nodist_data_DATA = $(php_generated)

EXTRA_DIST = $(php_in_sources) 

php/WHA/version.php: php/WHA/version.php.in _sedvars.sed
	( $(TEST) -e ./php/WHA || $(MKDIR) -p ./php/WHA ) && \
	  $(SED) -f _sedvars.sed $(srcdir)/php/WHA/version.php.in > php/WHA/version.php

local_cleanfiles = php/WHA/version.php
CLEANFILES = $(sedvars_mk_cleanfiles) $(local_cleanfiles)
SUFFIXES = $(sedvars_mk_suffixes)


if GENERATE_APIDOC


APIGENFLAGS = --title "API documentation"

apigen_sources = $(php_sources)

apigen-targets.mk: $(apigen_sources)
	@echo "generating apigen-targets.mk" && \
	(SFILES=`echo "$(apigen_sources)" | $(SED) -e 's/\([^ \t]\+\)/-s \1/g'` && \
	$(APIGEN) -d ./api-doc $(APIGENFLAGS) $${SFILES})  && \
	  ( $(PRINTF) 'apigen_targets = ' && \
	    ( $(FIND) ./api-doc -type f | $(SORT) | $(TR) '\n' ':' | \
	      $(SED) -e 's/:\+$$/\n/' -e 's/:\+/ \\\n                  /g' ) ) \
	    > ./apigen-targets.mk 

nobase_nodist_html_DATA = $(apigen_targets)

include apigen-targets.mk

$(apigen_targets): $(apigen_sources)
	(SFILES=`echo "$(apigen_sources)" | $(SED) -e 's/\([^ \t]\+\)/-s \1/g'` && \
	$(APIGEN) -d ./api-doc $(APIGENFLAGS) $${SFILES})

CLEANFILES += $(apigen_targets) 

endif
