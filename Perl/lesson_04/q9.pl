#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 4           #  
# Question 9         #
# 02/01/2007         #
######################
######################
#Algorithm that      #
#reads files and     #
#prints the lines    #
#that match a        #
#regular expression. #
######################
use strict;


while(<>){

	chomp;
	if(/^(nobody|grs)\b/){
	  print "$_\n";
	}
}

########################################
#Of course my question at this point is#
#, can you do a pattern match at any   #
#time, or can you only do it when there#
#is input? In put would make sense.    #
########################################
