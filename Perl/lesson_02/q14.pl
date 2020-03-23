#/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 2           #  
# Question 14        #
# 01/20/2007         #
######################
######################
#Algorithm using a   #
#post-test loop      #
#that specifies if a #
#value is in or out  #
#of range.           #
######################

use strict;

my $sentinel;

do {
	
	print "\nPick a number between 3 and 11 inclusive.\n(or type \"quit\" to quit): ";
	chomp ($sentinel = <STDIN>);

	if ($sentinel eq "quit"){
      print "\n";
	  exit;
	}
	
	if ($sentinel >= 3 && $sentinel <= 11){
      print "\n$sentinel is in range.\n";
	}
	else {
	  print "\n$sentinel is out of range.\n";
    }
	
} while ($sentinel ne "quit")
