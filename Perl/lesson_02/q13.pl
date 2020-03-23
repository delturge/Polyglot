#/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 2           #  
# Question 13        #
# 01/20/2007         #
######################
######################
#Algorithm using a   #
#pre-test loop       #
#that specifies if a #
#value is in or out  #
#of range.           #
######################

use strict;

my $sentinel;

while ($sentinel ne "quit"){
	print "\nPick a number between 3 and 11 inclusive.\n(or type \"quit\" to quit): ";
	chomp ($sentinel = <STDIN>);
	
	if ($sentinel eq "quit"){
	  print "\n";	
      last;
	}
	
	if ($sentinel >= 3 && $sentinel <= 11){
      print "\n$sentinel is in range.\n";
	}
	else {
	  print "\n$sentinel is out of range.\n";
    }	
}
