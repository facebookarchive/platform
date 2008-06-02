#/******BEGIN LICENSE BLOCK*******
#* 
#* Common Public Attribution License Version 1.0.
#*
#* The contents of this file are subject to the Common Public Attribution 
#* License Version 1.0 (the "License") you may not use this file except in 
#* compliance with the License. You may obtain a copy of the License at
#* http://developers.facebook.com/fbopen/cpal.html. The License is based 
#* on the Mozilla Public License Version 1.1 but Sections 14 and 15 have 
#* been added to cover use of software over a computer network and provide 
#* for limited attribution for the Original Developer. In addition, Exhibit A 
#* has been modified to be consistent with Exhibit B.
#* Software distributed under the License is distributed on an "AS IS" basis, 
#* WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License 
#* for the specific language governing rights and limitations under the License.
#* The Original Code is Facebook Open Platform.
#* The Original Developer is the Initial Developer.
#* The Initial Developer of the Original Code is Facebook, Inc.  All portions 
#* of the code written by Facebook, Inc are 
#* Copyright 2006-2008 Facebook, Inc. All Rights Reserved.
#*
#*
#********END LICENSE BLOCK*********/


PROJECT_ROOT = ../
PROJECT_NAME = test

AUTO_SOURCES = 1

CPPFLAGS += -I../src


LIBS = -L$(LIB_DIR) -lfbml \
	-L ../src/lib \
        -lmozutil_s \
        -lexpat_s \
        -lsaxp \
        -lunicharutil_s \
        -lxpcomcomponents_s \
        -lxpcomproxy_s \
        -lxpcomio_s \
        -lxpcomds_s \
        -lxpcomglue \
        -lxpcombase_s \
        -lxpcomthreads_s \
        -lxptcmd \
        -lxptcall \
        -lxptinfo \
        -lxpt \
        -lstring_s \
        -lplc4 \
        -lplds4 \
        -lnspr4 \
        -lpthread \
        -ldl \
        -L.. -latom \

include $(PROJECT_ROOT)/rules.mk
TARGETS = $(APP_TARGET)

all: $(TARGETS)
