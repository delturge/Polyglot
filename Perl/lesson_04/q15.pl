#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 4           #  
# Question 15        #
# 02/01/2007         #
######################
######################
#Algorithm that      #
#reads files and     #
#makes the fifth     #
#field all caps,     #
#the prints it.      #
######################
use strict;

my @split_Line;
my $joined_Line;

while (<>){
	
	chomp;
	@split_Line = split /:/, $_;           #split and store
	$split_Line[4] = "\U$split_Line[4]";   #isolate, manipulate, store
	$joined_Line = join ":", @split_Line;  #join and store
	print "$joined_Line\n";                #display output
}
	
#I want you to know, that I considered
#using the binding (=~) operator and 
#a conditional statement to change the 
#value of of $split_Line[4]. but I felt
#this was more direct.
