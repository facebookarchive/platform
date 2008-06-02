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

#include <stdio.h>
#include <stdlib.h>
#include "fbml.h"
#include <string>
#include <sys/time.h>
#include "test_fbml_parser.h"
#include "test_css_parser.h"
#include "test_js_parser.h"
#include "test_fbml_node.h"

using namespace std;

#define RUN_TESTSUITE(name)                                             \
  if (testsuite.empty() || testsuite == #name) {                        \
    printf(#name "......\n\n");                                         \
    name test;                                                          \
    if (test.RunTests(which)) {                                         \
      printf("\n" #name " OK\n\n");                                     \
    } else {                                                            \
      printf("\n" #name " #####>>> FAILED <<< #####\n\n");              \
    }                                                                   \
  }                                                                     \

///////////////////////////////////////////////////////////////////////////////

class Timer {
public:
  Timer(const char *name) {
    if (name) m_name = name;
    gettimeofday(&m_start, 0);
  }

  ~Timer() {
    long diff = getMicroSeconds();
    printf("%s: %ld us, ", m_name.c_str(), diff);
  }

  long getMicroSeconds() {
    struct timeval end;
    gettimeofday(&end, 0);
    return (end.tv_sec - m_start.tv_sec) * 1000000 +
      (end.tv_usec - m_start.tv_usec);
  }

private:
  std::string m_name;
  struct timeval m_start;
};

///////////////////////////////////////////////////////////////////////////////

char *translate_url(char *url, void *data) {
  return strdup("zzzmake");
}

int main(int argc, char **argv) {
  string testsuite;
  string which;
  if (argc >= 2) testsuite = argv[1];
  if (argc >= 3) which = argv[2];

  RUN_TESTSUITE(TestCSSParser);
  RUN_TESTSUITE(TestJSParser);
  RUN_TESTSUITE(TestFBMLParser);
  RUN_TESTSUITE(TestFBMLNode);

  return 0;
}
