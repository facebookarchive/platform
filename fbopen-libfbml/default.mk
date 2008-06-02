# /******BEGIN LICENSE BLOCK*******
#   *
#   * Common Public Attribution License Version 1.0.
#   *
#   * The contents of this file are subject to the Common Public Attribution
#   * License Version 1.0 (the "License") you may not use this file except in
#   * compliance with the License. You may obtain a copy of the License at
#   * http://developers.facebook.com/fbopen/cpal.html. The License is based
#   * on the Mozilla Public License Version 1.1 but Sections 14 and 15 have
#   * been added to cover use of software over a computer network and provide
#   * for limited attribution for the Original Developer. In addition, Exhibit A
#   * has been modified to be consistent with Exhibit B.
#   * Software distributed under the License is distributed on an "AS IS" basis,
#   * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
#   * for the specific language governing rights and limitations under the License.
#   * The Original Code is Facebook Open Platform.
#   * The Original Developer is the Initial Developer.
#   * The Initial Developer of the Original Code is Facebook, Inc.  All portions
#   * of the code written by Facebook, Inc are
#   * Copyright 2006-2008 Facebook, Inc. All Rights Reserved.
#   *
#   *
#   ********END LICENSE BLOCK*********/
#

.PHONY: clobber
clobber:
	@$(RM) $(SUB_INTERMEDIATE_FILES) *.o $(SUB_OBJECTS) \
	*.d *.gcno .sconsign *.merge-left.* *.merge-right.* *.working \
	lib$(PROJECT_NAME).so lib$(PROJECT_NAME).a *~ $(OBJECTS:.o=) core.* \
	$(filter-out $(SUB_PROGRAMS) $(SUB_LIB_TARGETS), $(TARGETS)) \
	$(wildcard $(patsubst %, %/*.d, $(SOURCE_SUBDIRS))) \
	$(wildcard $(patsubst %, %/*.gcno, $(SOURCE_SUBDIRS))) \
	$(wildcard $(patsubst %, %/*~, $(SOURCE_SUBDIRS))) \
	@$(RMDIR) gen-cpp
	@for mdir in $(SUB_PROGRAMS); do $(MAKE) -C $$mdir clobber; done
	@for mdir in $(SUB_LIB_TARGETS); do $(MAKE) -C $$mdir clobber; done

# delete all intermediate files without deleting targets
.PHONY: clean
clean:
	@$(RM) $(SUB_INTERMEDIATE_FILES) *.o $(SUB_OBJECTS) \
	*.d *.gcno .sconsign *.merge-left.* *.merge-right.* *.working \
	$(filter-out $(TARGETS),$(OBJECTS:.o=)) core.* *~ \
	$(wildcard $(patsubst %, %/*.d, $(SOURCE_SUBDIRS))) \
	$(wildcard $(patsubst %, %/*.gcno, $(SOURCE_SUBDIRS))) \
	$(wildcard $(patsubst %, %/*~, $(SOURCE_SUBDIRS)))
	@$(RMDIR) gen-cpp
	@for mdir in $(SUB_PROGRAMS); do $(MAKE) -C $$mdir clean; done
	@for mdir in $(SUB_LIB_TARGETS); do $(MAKE) -C $$mdir clean; done

# delete targets only
.PHONY: clear-targets
cleartargets:
	@$(RM) $(TARGETS)
	@for mdir in $(SUB_PROGRAMS); do $(MAKE) -C $$mdir cleartargets; done
	@for mdir in $(SUB_LIB_TARGETS); do $(MAKE) -C $$mdir cleartargets; done

# default no-op "make install"
.PHONY: install
install:
	@for mdir in $(SUB_PROGRAMS); do $(MAKE) -C $$mdir install; done
	@for mdir in $(SUB_LIB_TARGETS); do $(MAKE) -C $$mdir install; done

.PHONY: list-targets
list-targets:
	@echo $(TARGETS) | tr ' ' '\n'
	@for mdir in $(SUB_PROGRAMS); do $(MAKE) -C $$mdir list-targets; done
	@for mdir in $(SUB_LIB_TARGETS); do $(MAKE) -C $$mdir list-targets; done

.PHONY: list-sources
list-sources:
	@echo $(SOURCES) | tr ' ' '\n'
	@for mdir in $(SUB_PROGRAMS); do $(MAKE) -C $$mdir list-sources; done
	@for mdir in $(SUB_LIB_TARGETS); do $(MAKE) -C $$mdir list-sources; done

.PHONY: list-thrift-sources
list-thrift-sources:
	@echo $(SUB_THRIFT_SOURCES) | tr ' ' '\n'
	@for mdir in $(SUB_PROGRAMS); \
	do $(MAKE) -C $$mdir list-thrift-sources; \
	done
	@for mdir in $(SUB_LIB_TARGETS); \
	do $(MAKE) -C $$mdir list-thrift-sources; \
	done

.EXPORT_ALL_VARIABLES:;
unexport SUB_PROGRAMS SUB_LIB_TARGETS
