#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 2           #  
# Question 15        #
# 01/20/2007         #
######################
######################
#Algorithm that      #
#creates and prints  #
#an array in two     #
#different ways using#
#three functions.    #
######################

use strict;
################################################################################

&print_Array_shift(&create_Array);
&print_Array_pop(&create_Array);

################################################################################
sub create_Array {
	
   my   (@array_5_15);
   push (@array_5_15, 5..15);          #Using push to create array.
	
   @array_5_15;	
}

################################################################################
sub print_Array_shift {
   my @array_for_shifting = @_;
   my $shifted_element;
	
   print "\n";	
   
   foreach  (@array_for_shifting){
	   $shifted_element = shift (@_);  #Using shift to print array.
	   print "$shifted_element ";
   }
}

################################################################################
sub print_Array_pop {
    my @array_for_popping = @_;
	my $popped_element;
	
	print "\n";	
	
#	reverse (@array_for_popping);      #Optional, not in instuctions.
	
	foreach (@array_for_popping){
	   $popped_element = pop (@_);     #Using pop to print array.
	   print "$popped_element ";
	}
    print "\n\n";
}
