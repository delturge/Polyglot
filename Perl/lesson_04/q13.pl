#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 4           #  
# Question 13        #
# 02/01/2007         #
######################
######################
#Algorithm that      #
#reads files, swaps  #
#cheeta for alpaca,  #
#and prints to the   #
#screen.             #
######################
use strict;

while(<>){
	chomp;
	s/alpaca/cheeta/g;     #I have some style questions about this line.
	print "$_\n";
}
