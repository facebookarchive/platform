dnl /******BEGIN LICENSE BLOCK*******
dnl *
dnl * Common Public Attribution License Version 1.0.
dnl *
dnl * The contents of this file are subject to the Common Public Attribution
dnl * License Version 1.0 (the "License") you may not use this file except in
dnl * compliance with the License. You may obtain a copy of the License at
dnl * http://developers.facebook.com/fbopen/cpal.html. The License is based
dnl * on the Mozilla Public License Version 1.1 but Sections 14 and 15 have
dnl * been added to cover use of software over a computer network and provide
dnl * for limited attribution for the Original Developer. In addition, Exhibit A
dnl * has been modified to be consistent with Exhibit B.
dnl * Software distributed under the License is distributed on an "AS IS" basis,
dnl * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
dnl * for the specific language governing rights and limitations under the License.
dnl * The Original Code is Facebook Open Platform.
dnl * The Original Developer is the Initial Developer.
dnl * The Initial Developer of the Original Code is Facebook, Inc.  All portions
dnl * of the code written by Facebook, Inc are
dnl * Copyright 2006-2008 Facebook, Inc. All Rights Reserved.
dnl *
dnl *
dnl ********END LICENSE BLOCK*********/
dnl

PHP_ARG_ENABLE(fbml, whether to enable fbml support,
[ --enable-fbml      Enable fbml support])

if test "$PHP_FBML" != "no"; then
  FBML_SHARED_LIBADD="../libfbml.a ../src/lib/libmozutil_s.a ../src/lib/libexpat_s.a ../src/lib/libsaxp.a ../src/lib/libunicharutil_s.a ../src/lib/libxpcomcomponents_s.a ../src/lib/libxpcomproxy_s.a ../src/lib/libxpcomio_s.a ../src/lib/libxpcomds_s.a ../src/lib/libxpcomglue.a ../src/lib/libxpcombase_s.a ../src/lib/libxpcomthreads_s.a ../src/lib/libxptcmd.a ../src/lib/libxptcall.a ../src/lib/libxptinfo.a ../src/lib/libxpt.a ../src/lib/libstring_s.a ../src/lib/libplc4.a ../src/lib/libplds4.a ../src/lib/libnspr4.a ../libatom.a -lstdc++ -lpthread"

  PHP_SUBST(FBML_SHARED_LIBADD)

  PHP_NEW_EXTENSION(fbml, fbml.c, $ext_shared)
fi
