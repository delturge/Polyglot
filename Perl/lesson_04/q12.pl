#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 4           #  
# Question 12        #
# 02/01/2007         #
######################
######################
#Algorithm that      #
#splits and stores,  #
#joins and stores,   #
#and prints new      #
#output.             #
######################
use strict;

my @split_List;
my $joinedLines;

while(<>){
	chomp;
	
#	if (s/:$/|/){}
	
	@split_List  = (split /:/, $_);
	$joinedLines = join "|", @split_List;
	print "$joinedLines\n";

	
}


#After running the out put through "wc",
#I determined there was a ten character
#difference between passwd.txt and the
#output of this program. The ten characters
#are the colons at the end of 10 lines.
#That is why the condiitional statement
#exists. I did not know if the
#colons that get left out by split and 
#join would be significant, so I commented
#out the conditional statement.
