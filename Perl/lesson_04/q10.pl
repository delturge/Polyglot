#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 4           #  
# Question 10        #
# 02/01/2007         #
######################
######################
#Algorithm that      #
#reads lines, splits #
#and stores them in  #
#an array, then a    #
#hash, and prints.   #
######################
use strict;

my %password_hash;        #will store passwords and values
my @password_array;       #will store second field from password.txt, in @password_array[1]
my $password_key;         #used to output keys in second while loop 
my $password_value;       #used to output values in second while loop

while (<>){

    chomp;
	@password_array = split /:/, $_ ;                    #splitting and storing
	
	if (! exists $password_hash{"@password_array[1]"}){  #using array to test and populate hash
	  $password_hash{"@password_array[1]"}  = 1;         #because the hash will be empty at first
   }else{
	  $password_hash{"@password_array[1]"}++;         #because this will occur after a key exists
   }
}

while ( ($password_key, $password_value) = each %password_hash){              #using loop and each to get at hash
	
	printf "%s was encountered %d times.\n", $password_key, $password_value;  #using formatted string, adding abstraction
}
