#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 5           #  
# Question  8        #
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

my $employee_Data;
my @employee_Fields;

open EMPLOYEE_DATA, "<", "telData.txt";            #Thought I'd start using file handles

while (<EMPLOYEE_DATA>){                           #If you never use file handles, you're in the dark.
	chomp ($employee_Data = $_);
	@employee_Fields = split /\|/, $employee_Data;
	print "\n$employee_Fields[1] $employee_Fields[0] $employee_Fields[2]\n" if $employee_Fields[5] eq "AZ" && $employee_Fields[2] > 200 || $employee_Fields[6] == 703;
}
