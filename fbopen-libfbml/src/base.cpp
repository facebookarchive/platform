/******BEGIN LICENSE BLOCK*******
*
* Common Public Attribution License Version 1.0.
*
* The contents of this file are subject to the Common Public Attribution
* License Version 1.0 (the "License") you may not use this file except in
* compliance with the License. You may obtain a copy of the License at
* http://developers.facebook.com/fbopen/cpal.html. The License is based
* on the Mozilla Public License Version 1.1 but Sections 14 and 15 have
* been added to cover use of software over a computer network and provide
* for limited attribution for the Original Developer. In addition, Exhibit A
* has been modified to be consistent with Exhibit B.
* Software distributed under the License is distributed on an "AS IS" basis,
* WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
* for the specific language governing rights and limitations under the License.
* The Original Code is Facebook Open Platform.
* The Original Developer is the Initial Developer.
* The Initial Developer of the Original Code is Facebook, Inc.  All portions
* of the code written by Facebook, Inc are
* Copyright 2006-2008 Facebook, Inc. All Rights Reserved.
*
*
********END LICENSE BLOCK*********/

#include "base.h"

// we allow clients to overwrite this handler
static void default_on_bad_alloc() {
  throw std::bad_alloc();
}
void (*libfbml_on_bad_alloc)() = default_on_bad_alloc;

void *m(void *p) {
  if (!p) libfbml_on_bad_alloc();
  return p;
}

char *m(char *p) {
  if (!p) libfbml_on_bad_alloc();
  return p;
}

const char *m(const char *p) {
  if (!p) libfbml_on_bad_alloc();
  return p;
}

void UTF16ToStdString(std::string &out, const nsString &s) {
  out.reserve(s.Length());

  nsString::const_iterator start, end;
  for (s.BeginReading(start), s.EndReading(end); start != end; ++start) {
    if ((((unsigned int)(*start)) & 0xFF00) == 0) {
      if (*start) {
        out += (char)(*start);
      }
    } else {
      nsString s(*start);
      out += NS_LossyConvertUTF16toASCII(s).get();
    }
  }
}

/**
 * Similar to implementation of above UTF16toStdString function, except that
 * Unicode characters with values between 128 and 2^15 - 1 inclusive are
 * treated differently here.  The above function works fine for all characters
 * outside the [128, 2^15) range, but special case needs to be made to convert UTF16
 * characters (that's how the Mozilla parser stores them) to UTF8 (instead of
 * ASCII).
 *
 * @param out the C++ string where the UTF-8-compliant version of a string should
 *            be placed.
 * @param s the nsString storing the UTF-16 version of the string being
 *          converted.
 */

void UTF16ToUTF8String(std::string &out, const nsString &s)
{
  out.reserve(s.Length());
  nsString::const_iterator start, end;

  for (s.BeginReading(start), s.EndReading(end); start != end; ++start) {
    if ((((unsigned int)(*start)) & 0xFF80) == 0) { // ASCII is less than 128
      if (*start) {
        out += (char)(*start);
      }
    } else if ((((unsigned int)(*start)) & 0x8000) == 0) { // everything else with MSB = 0
      nsString s(*start);
      out += NS_ConvertUTF16toUTF8(s).get();
    } else { // everything with MSB = 1
      nsString s(*start);
      out += NS_LossyConvertUTF16toASCII(s).get();
      // two-char UTF-16 can be laid down as two ASCII bytes
    }
  }
}

