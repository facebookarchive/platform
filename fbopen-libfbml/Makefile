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

VERSION = 1.2.0
PROJECT_ROOT = .
PROJECT_NAME = fbml

AUTO_SOURCES = 1
SOURCE_SUBDIRS = ext

# we really just use this as what LIB_TARGETS depends on
CODEGEN_TARGETS = libatom.a

LIB_TARGETS = \
	src \

PROGRAMS = \
	test \

CPPFLAGS = -Isrc

LIBS = \
	$(LIB_DIR)/libfbml.a \
	src/lib/libmozutil_s.a \
	src/lib/libexpat_s.a \
	src/lib/libsaxp.a \
	src/lib/libunicharutil_s.a \
	src/lib/libxpcomcomponents_s.a \
	src/lib/libxpcomproxy_s.a \
	src/lib/libxpcomio_s.a \
	src/lib/libxpcomds_s.a \
	src/lib/libxpcomglue.a \
	src/lib/libxpcombase_s.a \
	src/lib/libxpcomthreads_s.a \
	src/lib/libxptcmd.a \
	src/lib/libxptcall.a \
	src/lib/libxptinfo.a \
	src/lib/libxpt.a \
	src/lib/libstring_s.a \
	src/lib/libplc4.a \
	src/lib/libplds4.a \
	src/lib/libnspr4.a \
	libatom.a \
        -lstdc++ -lpthread

include $(PROJECT_ROOT)/rules.mk
TARGETS = $(PROGRAMS)

all: $(TARGETS)

libatom.a: src/lib/os_Linux_x86.o
	$(AR) $@ $^
