#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 6           #  
# Question  2        #
# 02/18/2007         #
######################
######################
#Algorithm that      #
#lists all files in  #
#the lesson5 folder  #
#that are desired  by#
#globbinh.           #
######################
use strict;

my $dir = "./test/lesson5";
my @selected_File = <$dir/file*5>;
print "The files are: @selected_File\n";
