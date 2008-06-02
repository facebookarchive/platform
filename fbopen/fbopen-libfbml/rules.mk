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

###############################################################################
#
# If you want to take advantage of this rules.mk, define one of these prefined
# project types, and there is very minimum Makefile lines to write.
#
#    EXCLUDES =              # any .c or .cpp files to exclude from build
#    include $(PROJECT_ROOT)/rules.mk
#    all: $(TARGETS)         # one should always have this line unchanged
#
# 1. Static Library Project:
#
#    PROJECT_NAME = xxx      # final lib is named as libxxx.a
#    TARGETS = $(STATIC_LIB) # add more targets than libxxx.a
#
# 2. Shared Library Project:
#
#    PROJECT_NAME = xxx      # final lib is named as libxxx.so
#    TARGETS = $(SHARED_LIB) # add more targets than libxxx.so
#
# 3. Application Project:
#
#    PROJECT_NAME = xxx      # final executable is named as xxx
#    TARGETS = $(APP_TARGET) # add more targets than xxx
#
# 4. Multi-Target Project:
#
#    CODEGEN_TARGETS = <list of code generation targets>
#    LIB_TARGETS = <list of subdirs that build libraries>
#    PROGRAMS = <list of subdirs that build executables>
#    TARGETS = $(PROGRAMS)   # add more targets than xxx
#
# 5. Mono-Target Project:
#
#    CODEGEN_TARGETS = <list of code generation targets>
#    LIB_TARGETS = <list of subdirs that build libraries>
#    MONO_TARGETS = <each of .cpp builds one executable>
#    TARGETS = $(MONO_TARGETS) # add more targets than xxx
#
# The following targets are automatically defined:
#
#  make clobber: delete all intermediate files and built targets
#  make clean: delete all intermediate files without deleting targets
#  make cleartargets: delete targets only
#  (check default.mk for more)
#
# If there are extar files to remove when "make clobber" or "make clean", add
# them to $(INTERMEDIATE_FILES).
#
###############################################################################
# Command line switches. For example, "make PRODUCTION=1".

# This normally generates debug symbols, but you may also use this in your
# code to output extra debugging information.
#DEBUG = 1

# This normally adds -O3 tag to generate the most optimized code targeted for
# production build.
#PRODUCTION = 1

# For GNU profiler - gprof.
#PROFILE = 1

# For GNU coverage - gcov.
#COVERAGE = 1

###############################################################################
# Directories

MKDIR = mkdir -p
RMDIR = rm -fR
LIB_DIR = $(PROJECT_ROOT)
INTERFACE_DIR = $(PROJECT_ROOT)/interface

###############################################################################
# Code Generation

THRIFT_FILES += \
  $(wildcard *.thrift) \
  $(wildcard interface/*.thrift) \
  $(wildcard if/*.thrift) \
  $(wildcard $(patsubst %, %/*.thrift, $(SOURCE_SUBDIRS))) \
  $(wildcard $(patsubst %, %/if/*.thrift, $(SOURCE_SUBDIRS))) \

THRIFT_SOURCES += \
  $(patsubst %.thrift, gen-cpp/%_service.cpp,   $(notdir $(THRIFT_FILES))) \

THRIFT_EXTRA_SOURCES += \
  $(patsubst %.thrift, gen-cpp/%_types.cpp,     $(notdir $(THRIFT_FILES))) \
  $(patsubst %.thrift, gen-cpp/%_constants.cpp, $(notdir $(THRIFT_FILES))) \

###############################################################################
# Source Files

ifdef AUTO_SOURCES

GENERATED_CXX_SOURCES += \
  $(THRIFT_SOURCES) $(THRIFT_EXTRA_SOURCES)

CXX_SOURCES += \
  $(wildcard *.cpp) \
  $(wildcard $(patsubst %, %/*.cpp, $(SOURCE_SUBDIRS)))

C_SOURCES += \
  $(wildcard *.c) \
  $(wildcard $(patsubst %, %/*.c, $(SOURCE_SUBDIRS)))

endif

GENERATED_SOURCES = \
  $(GENERATED_CXX_SOURCES) $(GENERATED_C_SOURCES) $(GENERATED_CPP_SOURCES)
ALL_SOURCES += $(CXX_SOURCES) $(C_SOURCES) $(GENERATED_SOURCES)
INTERMEDIATE_FILES += $(GENERATED_SOURCES)
SOURCES += $(filter-out $(EXCLUDES), $(ALL_SOURCES))
OBJECTS += $(patsubst %.cpp, %.o, $(SOURCES:.c=.o))

STATIC_LIB = $(LIB_DIR)/lib$(PROJECT_NAME).a
SHARED_LIB = $(LIB_DIR)/lib$(PROJECT_NAME).so
APP_TARGET = $(PROJECT_NAME)
MONO_TARGETS = $(filter-out $(APP_TARGET), $(patsubst %.cpp, %, $(wildcard *.cpp)))

###############################################################################
# Compilation

# Both $(CC) and $(CXX) will now generate .d dependency files.
CPPFLAGS += -MMD -fPIC
SHOW_COMPILE = 1
SHOW_LINK = 1
# We really just have to list all include paths here, because building
# different .o files with different include search paths is dangerous. Downside
# is it might take slightly longer to resolve a <include.h>, therefore one
# should always use "include.h" for files that are local to the project.

ifdef PROJECT_ROOT

CXXFLAGS += \

endif

CXXFLAGS += \
  -isystem /usr/local/include/boost-1_33_1 \

ifdef EXTERNAL_DIR

CXXFLAGS += \

endif

CXXFLAGS += \
  -D_GNU_SOURCE \
  -D_REENTRANT=1 -D_PTHREADS=1 -pthread \
  -ftemplate-depth-60 \

ifndef NO_WALL
CXXFLAGS += -Wall -Woverloaded-virtual
endif

ifndef NO_WERROR
CXXFLAGS += -Werror
endif

ifdef DEBUG
CPPFLAGS += -DDEBUG -g
endif

ifdef PRODUCTION
CPPFLAGS += -DPRODUCTION -O3
endif

ifdef PROFILE
CPPFLAGS += -pg
endif

ifdef COVERAGE
CPPFLAGS += -fprofile-arcs -ftest-coverage
endif

###############################################################################
# Linking

AR = ar -crs
LD = g++ -o

# Add library search paths here.
LDFLAGS	+= \
  -L $(LIB_DIR) \
  -L /usr/local/lib \

ifdef PROFILE
LDFLAGS += -pg
endif

ifdef COVERAGE
LDFLAGS += -fprofile-arcs
endif

###############################################################################
# Libraries
#
# 1. Base Libraries
#
# These have to be libraries that nearly ALL programs need to link with. Do
# NOT add something that not everyone wants.

LINK_LIBS = -lpthread -lstdc++ -lz

# 2. Common Libraries
#
# Common but not essential.

BOOST_LIBS = -L /usr/local/boost/lib -lboost_filesystem-gcc-1_33_1

# -----------------------------------------------------------------------------
# 3. External Libraries
#
# Define any external libraries here.

# -----------------------------------------------------------------------------
# 4. Facebook Libraries
#
# Define any libraries we wrote internally.

###############################################################################
# Dependencies

# This is to make sure "make" without any target will actually "make all".
overall: all

# Suppressing no rule errors
%.d:;

DEPEND_FILES := $(patsubst %.cpp, %.d, $(ALL_SOURCES:.c=.d))
ifneq ($(DEPEND_FILES),)
-include $(DEPEND_FILES)
endif

dep_libs = $(filter $(patsubst -L%,, $(patsubst -l%, $(LIB_DIR)/lib%.a, $(1))), $(wildcard $(LIB_DIR)/*))

DEP_LIBS += $(call dep_libs, $(LIBS))

###############################################################################
# Predefined Targets

ifdef SHOW_COMPILE
define COMPILE_CXX
$(CXX) -c $(CPPFLAGS) $(CXXFLAGS) -o $@ $<
endef
else
define COMPILE_CXX
@echo 'Compiling $<...'
@$(CXX) -c $(CPPFLAGS) $(CXXFLAGS) -o $@ $<
endef
endif

ifdef SHOW_LINK
define LINK_OBJECTS
$(LD) $@ $(LDFLAGS) $(filter %.o,$^) $(LIBS)
endef
else
define LINK_OBJECTS
@echo 'Linking $@...'
@$(LD) $@ $(LDFLAGS) $(filter %.o,$^) $(LIBS)
endef
endif

%:%.o

%:%.c

%:%.cpp

ifdef SHOW_COMPILE

$(CXX_SOURCES:%.cpp=%.o) $(GENERATED_CXX_SOURCES:%.cpp=%.o): %.o:%.cpp
	$(CXX) -c $(CPPFLAGS) $(CXXFLAGS) -o $@ $<

$(C_SOURCES:%.c=%.o) $(GENERATED_C_SOURCES:%.c=%.o): %.o:%.c
	$(CC) -c $(CPPFLAGS) -o $@ $<

$(GENERATED_C_SOURCES:%.c=%.o): %.o:%.c
	$(CXX) -c $(CPPFLAGS) $(CXXFLAGS) -o $@ $<
else

$(CXX_SOURCES:%.cpp=%.o) $(GENERATED_CXX_SOURCES:%.cpp=%.o): %.o:%.cpp
	@echo 'Compiling $<...'
	@$(CXX) -c $(CPPFLAGS) $(CXXFLAGS) -o $@ $<

$(C_SOURCES:%.c=%.o) $(GENERATED_C_SOURCES:%.c=%.o): %.o:%.c
	@echo 'Compiling $<...'
	@$(CC) -c $(CPPFLAGS) -o $@ $<

$(GENERATED_CPP_SOURCES:%.c=%.o): %.o:%.c
	@echo 'Compiling $<...'
	@$(CXX) -c $(CPPFLAGS) $(CXXFLAGS) -o $@ $<
endif

.EXPORT_ALL_VARIABLES:;
unexport THRIFT_FILES THRIFT_SOURCES THRIFT_EXTRA_SOURCES CXX_SOURCES C_SOURCES GENERATED_CXX_SOURCES GENERATED_C_SOURCES GENERATED_CPP_SOURCES ALL_SOURCES SOURCES OBJECTS DEPEND_FILES CPPFLAGS CXXFLAGS LDFLAGS PROGRAMS LIB_TARGETS DEP_LIBS

# Since these variables start with += in this file, when calling submake,
# they will not start with empty list. SUB_XXX will always start with empty.
SUB_SOURCE_SUBDIRS = $(SOURCE_SUBDIRS)
SUB_PROGRAMS = $(PROGRAMS)
SUB_LIB_TARGETS = $(LIB_TARGETS)
SUB_OBJECTS = $(OBJECTS)
SUB_THRIFT_SOURCES = $(THRIFT_SOURCES)
SUB_INTERMEDIATE_FILES = $(INTERMEDIATE_FILES)

.DEFAULT:
	@$(MAKE) --no-print-directory -f $(PROJECT_ROOT)/default.mk $@

$(OBJECTS): $(GENERATED_SOURCES)

ifdef SHOW_LINK

$(SHARED_LIB): $(OBJECTS)
	$(CXX) -shared -fPIC -g -Wall -Werror -Wl,-soname,$@ -o $@ $(OBJECTS)

$(STATIC_LIB): $(OBJECTS)
	$(AR) $@ $(OBJECTS)

$(MONO_TARGETS): %:%.o $(DEP_LIBS)
	$(LD) $@ $(LDFLAGS) $< $(LIBS)

else

$(SHARED_LIB): $(OBJECTS)
	@echo 'Linking $@...'
	@$(CXX) -shared -fPIC -g -Wall -Werror -Wl,-soname,$@ -o $@ $(OBJECTS)

$(STATIC_LIB): $(OBJECTS)
	@echo 'Linking $@...'
	@$(AR) $@ $(OBJECTS)

$(MONO_TARGETS): %:%.o $(DEP_LIBS)
	@echo 'Linking $@...'
	@$(LD) $@ $(LDFLAGS) $< $(LIBS)

endif

$(APP_TARGET): $(OBJECTS) $(DEP_LIBS)
	$(LINK_OBJECTS) $(LINK_LIBS)

.PHONY: $(LIB_TARGETS)
$(LIB_TARGETS): $(CODEGEN_TARGETS)
	@$(MAKE) --no-print-directory -C $@

.PHONY: $(PROGRAMS)
$(PROGRAMS): $(LIB_TARGETS)
	@$(MAKE) --no-print-directory -C $@

$(THRIFT_SOURCES:_service.cpp=.thrift): $(THRIFT_FILES)
	@$(MKDIR) gen-cpp
	@cp -f $^ gen-cpp/
	@cp -f $^ $(INTERFACE_DIR)

$(THRIFT_EXTRA_SOURCES): $(THRIFT_SOURCES)
$(THRIFT_SOURCES): %_service.cpp:%.thrift /usr/local/bin/thrift
	thrift -I $(INTERFACE_DIR) -cpp $<
	@ln -fs `egrep -o "^service (.*?) " $< | cut -d' ' -f2`.cpp $@
