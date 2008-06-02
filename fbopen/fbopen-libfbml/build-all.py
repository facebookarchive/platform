#!/usr/bin/env python

# /******BEGIN LICENSE BLOCK*******
#  *
#  * Common Public Attribution License Version 1.0.
#  *
#  * The contents of this file are subject to the Common Public Attribution
#  * License Version 1.0 (the "License") you may not use this file except in 
#  * compliance with the License. You may obtain a copy of the License at 
#  * http://developers.facebook.com/fbopen/cpal.html. The License is based
#  * on the Mozilla Public License Version 1.1 but Sections 14 and 15 have
#  * been added to cover use of software over a computer network and provide
#  * for limited attribution for the Original Developer. In addition, Exhibit A
#  * has been modified to be consistent with Exhibit B. 
#  * Software distributed under the License is distributed on an "AS IS" basis,
#  * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
#  * for the specific language governing rights and limitations under the License.
#  * The Original Code is Facebook Open Platform.
#  * The Original Developer is the Initial Developer.
#  * The Initial Developer of the Original Code is Facebook, Inc.  All portions
#  * of the code written by Facebook, Inc are
#  * Copyright 2006-2008 Facebook, Inc. All Rights Reserved.
#  *
#  *
#  ********END LICENSE BLOCK*********/

# Simple script in place to configure, make, and sudo make install
# all of the open source tools used by libfbml.  It then goes on
# to actually install libfbml as well.

from os import *
from os.path import *
import sys;

# Helper function designed to expand a zipped tar file, where the zip executable was either gunzip
# or bunzip2.  We untar the zipped, tarred file in one of two ways, relying (not surprisingly) on
# the file extension to determine which flags should be passed to the tar executable.  Only
# .gz and .bz2 extensions are supported.

def expandPackage(package):
    if package.endswith(".tar.gz"):
        result = system("tar zxfv " + package);
    elif package.endswith(".tar.bz2"):
        result = system("tar jxfv " + package);
    else:
        raise ValueError, "Only \"tar.gz\" and \"tar.bz2\" are expected.  Please update the implementation to handle other extensions."
    if result != 0:
        print "Attempt to decompress \"%s\" failed.  Aborting..." % package

# Takes a string of the form "<base-name>.tar.gz" or "<base-name>.tar.bz2" and returns
# just the "<base-name>" part.  The implementation is completely brute force but self explanatory.

def stripCompressionExtensions(package):
    if package.endswith(".tar.gz"):
        return package[0:-len(".tar.gz")]
    if package.endswith(".tar.bz2"):
        return package[0:-len(".tar.bz2")]
    raise ValueError, "Only \"tar.gz\" and \"tar.bz2\" are expected.  Please update the implementation to handle other extensions."

# Routine that creates a soft link to the specified file within the specified sourcedir.
# The soft link is laid down, using the same filename, in the specified destdir.

def createSoftLink(sourcedir, filename, destdir, commonparentdir):
    command = "find " + sourcedir + " -name " + filename + " -print"
    output = popen(command)
    allmatches = output.readlines()
    for match in allmatches:
        match = match.strip();
        if not islink(match):
            print "Soft linking to " + match
            command = "ln -s " + commonparentdir + sep + match + " " + destdir + sep + filename
            system(command)
            return
    print "Uh oh!  Couldn't find " + filename + " anywhere.  Aborting...."
    sys.exit(1)

# subdirectories is the ordered list of directories into which we should
# descend, ./configure, make, sudo make install, and ascend out of.  The
# order is important, since in some cases, tools than come later in the
# list depend on tools that come before it.

packages = ('pkg-config-0.20.tar.gz', 'glib-2.14.6.tar.gz', 'atk-1.9.1.tar.bz2', 'freetype-2.3.4.tar.gz',
            'fontconfig-2.3.97.tar.gz', 'libpng-1.2.25.tar.gz', 'cairo-1.2.6.tar.gz', 'tiff-3.7.4.tar.gz', 'pango-1.18.4.tar.bz2',
            'gtk+-2.10.13.tar.bz2', 'libIDL-0.8.8.tar.gz', 'libXft-2.1.12.tar.gz', 'xproto-7.0.7.tar.gz',
            'xrender-0.8.3.tar.bz2')

# commands maps each of the above packages to the sequence of command line instructions
# that need to be executed in order to install that component.  All of these commands
# are executed within the subdirectory created by unzipping and untarring the package.

commands = { 'pkg-config-0.20.tar.gz': ("./configure", "make", "sudo make install"),
             'glib-2.14.6.tar.gz': ("./configure", "make", "sudo make install"),
             'atk-1.9.1.tar.bz2': ("./configure", "make", "sudo make install"),
             'freetype-2.3.4.tar.gz': ("./configure", "make", "sudo make install"),
             'fontconfig-2.3.97.tar.gz': ("./configure", "make", "sudo make install"),
             'libpng-1.2.25.tar.gz' : ("./configure", "make", "sudo make install"),
             'cairo-1.2.6.tar.gz': ("./configure", "make", "sudo make install"),
             'tiff-3.7.4.tar.gz': ("./configure", "make", "sudo make install"),
             'pango-1.18.4.tar.bz2': ("./configure", "make", "sudo make install", "sudo cp pangocairo.pc /usr/local/lib/pkgconfig"),
             'gtk+-2.10.13.tar.bz2': ("./configure", "make", "sudo make install"),
             'libIDL-0.8.8.tar.gz': ("./configure", "make", "sudo make install"),
             'libXft-2.1.12.tar.gz': ("./configure", "make", "sudo make install"),
             'xproto-7.0.7.tar.gz': ("./configure", "make", "sudo make install"),
             'xrender-0.8.3.tar.bz2': ("./configure", "make", "sudo make install") }

dependenciesdir = "dependencies"
chdir(dependenciesdir)
for package in packages:
    # descend into the directory, configure, make, sudo make install, and ascend
    expandPackage(package)
    subdirectory = stripCompressionExtensions(package)
    chdir(subdirectory);
    for command in commands[package]: # loop through all the commands relevant to installation of the package being installed
        result = system(command)
        if result != 0:
            print "Running \"%s\" within the \"%s\" subdirectory failed with error code %d.  Aborting script..." % (command, subdirectory, result)
            sys.exit(result)
    chdir("..")

# got this far, you're good to actually configure and make firefox.  No need to install it, because
# we're interested only in the source files and the archive files, which the make system for libfbml references.

package = "firefox-2.0.0.4-source.tar.bz2"
result = system("tar jxfv " + package)
mozilla = "mozilla"
chdir(mozilla)
for command in ("./configure --enable-application=browser --enable-system-cairo", "make"):
    result = system(command)
    if result != 0:
        print "Running \"%s\" within the \"%s\" subdirectory failed with error code %d.  Aborting script..." % (command, mozilla, result)
        sys.exit()

# rise out of mozilla folder, and rise even further to top-level directory
chdir("..")
chdir("..")

# set up soft links to a distinguished set of .a files (and one .o file).  The build system supporting
# libfbml needs to pretend that these .o/.a files exist in ./src/lib/ so that libatom.a and libfbml.a can
# can be built.  All of the relative paths are hard-coded.

print "Searching for all of the Mozilla object files and archives that libfbml depends on"
archivefiles = ("os_Linux_x86.o", "libxptinfo.a", "libxptcmd.a", "libxptcall.a", "libxpt.a", "libxpcomthreads_s.a",
                "libxpcomproxy_s.a", "libxpcomio_s.a", "libxpcomglue_s.a", "libxpcomglue.a", "libxpcomds_s.a",
                "libxpcomcomponents_s.a", "libxpcombase_s.a", "libunicharutil_s.a", "libstrres_s.a",
                "libstring_s.a", "libsaxp.a", "libplds4.a", "libplc4.a", "libnspr4.a", "libmozutil_s.a",
                "libexpat_s.a")

for filename in archivefiles:
    system("rm -f src/lib/" + filename) # force the removal of previously set up soft links
    createSoftLink(dependenciesdir + sep + mozilla, filename, "src" + sep + "lib", ".." + sep + "..")

result = system("make")
if result != 0:
    print "Failed to make libfbml.... Aborting"
    sys.exit(result)

# finally, descend into PHP extension directory and push new PHP extension back behind apache
subdirectory = "ext"
chdir(subdirectory)
for command in ("phpize", "./configure", "make", "sudo make install"):
    result = system(command)
    if result != 0:
        print "Running \"%s\" within the \"%s\" subdirectory failed with error code %d.  Aborting script..." % (command, subdirectory, result)
        sys.exit(result)

print "Build complete.  You'll need to restart Apache before the new PHP extension can be used."
