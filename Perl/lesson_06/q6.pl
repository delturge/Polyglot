#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 6           #  
# Question 6         #
# 02/15/2007         #
######################
######################
#Algorithm that      #
#reads a file, splits#
#and stores each line#
#, and prints lines  #
# that fit.          #
######################
use strict;

my $dir = "./test/lesson5";

foreach (<$dir/file*5>){
	my @sliced_Path_Parts = split /\//, $_;
	my $file_Name_Only = $sliced_Path_Parts[3];
	print "The file name is: $file_Name_Only\n";
}
