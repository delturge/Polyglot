#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 5           #  
# Question  4        #
# 02/07/2007         #
######################
######################
#Algorithm that      #
#reads a file, splits#
#and stores each line#
#, and prints lines  #
# that fit.          #
######################
use strict;

my $telData_line;
my @telData_field;

print "\n\tWalmart Revised Promotion Policy\n\n";

while (<>){
	
	chomp($telData_line = $_);
	@telData_field = split /\|/, $telData_line;

	unless ($telData_fields[2] <= 200){
		print "$telData_field[1] $telData_field[0]\n\n";
   }else {
	    next;
   }
}
