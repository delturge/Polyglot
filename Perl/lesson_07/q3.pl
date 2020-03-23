#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 7           #  
# Question 3         #
# 02/27/2007         #
######################
######################
#                    #
#                    #
#                    #
#                    #
#                    #
######################
use strict;

&Get_Character;

sub Get_Character {
	while (1){
		print "\n\nEnter a characer to see it's ASCII value or \"quit\" to exit. ";
    	chomp (my $printable_Character = <STDIN>);
		
	 	if($printable_Character eq "quit"){
			last;
    	}else{
			&Display_ASCII_Value($printable_Character);
		}	
	}

}

sub Display_ASCII_Value {
		if ( @_ != 1){
			print "\n\nI pitty the fool who enters more than one character!";
		}else{	
		    my $letter = $_[0];
			my $unpacked_Value = unpack ("C", $letter);
		    print "\n$letter has an ASCII value of $unpacked_Value\n\n" ;
		}

}
