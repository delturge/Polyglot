#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 2           #  
# Question 11        #
# 01/19/2007         #
######################
######################
#Algorithm that adds #
#and prints the sum  #
#of two negative     #
#numbers using       #
#sub-routines.       #
######################

use strict;
################################################################################
											       #At first I used a global
&show_neg_sum(&add_neg_numbers(&get_neg_numbers)); #array, but I wanted to see											   
											       #the program work without it.
################################################################################

sub get_neg_numbers {                    #Gather input. Output both values.
	
  my $campaigns = 0;                     #Counter, priming the loop, for clarity
  my $candidate;	                     #Temporary variable, will validate in loop
  my @electable;                         #Array for holding validated, negative numbers

  print "\n\t\t\tNegative Number Adder (Limited Edition)!\n\nEnter a negative number (Hit Enter): \n\n";
	
  while ($campaigns != 2){                         #Data validation loop
	  chomp ($candidate = <STDIN>);                #Get user input
	  
      if ($candidate <= -1 && $campaigns < 1){     #Print this message after one valid input.
	    push (@electable, $candidate) ;            #Add first neg num to array
		print "\nThat's good!\nEnter another: ";   #Increment loop counter
		$campaigns++;  
	  }
	  elsif ($candidate <= -1 && $campaigns == 1){ #Increment $campaigns
        push (@electable, $candidate) ;            #Add 2nd neg number to array
		$campaigns++;                              #Final loop increment
	  }
	  else{                                        #Print when argument is invalid
        print "\nFull edition needed\nto use that number.\nTry again!: ";
      }
  }
  @electable;                                      #The output, two negative values
}
################################################################################

sub add_neg_numbers {                                #Gets input from get_neg_numbers
  my ($neg_num_one, $neg_num_two) = @_;              #Naming parameters
  my ($sum) = ($neg_num_one + $neg_num_two);         #Add arguments, return value of sum
}

################################################################################

sub show_neg_sum {                                   #Gets input from add_neg_numbers
  my ($sum) = @_;                                             #Naming parameter
  print "\nThe sum of your\nnegative numbers is: $sum\n\n" ;  #Display sum
}

################################################################################
