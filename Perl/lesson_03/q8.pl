#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 3           #  
# Question 8         #
# 01/26/2007         #
######################
######################
#Algorithm that      #
#compares user input #
#strings to keys of a#
#hash.               #
#                    #
######################

use strict;

my %passwords = (                       #Just trying this format out.
   "barney" => "llama",
    "betty" => "camel", 
	"wilma" => "llama", 
	"fred"  => "alpaca",
   );

my $user_string;                            #holds value of user input
my @passwords_keys = keys (%passwords);     #holds keys of %passwords
my $loop_counter = 0;                       #loop control for searching @passwords_keys
my $search_limit = 0;                       #sentinel for search loop

while (chomp ($user_string = <STDIN>)){     #gathers user input

	while ($user_string ne $passwords_keys[$loop_counter] && $loop_counter < ($search_limit = @passwords_keys)){  #searches for a match till number of elements in the array is reached       
		$loop_counter++;                    #helps find a match
	}	

	if ($user_string eq $passwords_keys[$loop_counter]){   #conditional true
      print "\"$user_string\" maps to \"$passwords{$user_string}\" in the passwords hash.\n";
      $loop_counter = 0;             #set search loop control to origin                      
	}else{                                      #conditional false
   	  print "\"$user_string\" is not in the passwords hash.\n";
      $loop_counter = 0;             #set search loop control to origin
	}	
}


# You know, I'm not going to lie. This program could probably
# be written better. Needed $search_limit to stop the search.
