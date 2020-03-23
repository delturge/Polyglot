#!/usr/bin/perl
# Anthony E. Rutledge, CIS 248
# Question 7 from lesson 1.
################################################################
# Write a Perl script, called q7.pl, that prompts the user     #
# for their name and age in years. The program will then print #
# the users name and age age/3 times. (That is the user's age  # 
# divided by 3)                                                # 
#   														   # 
# Constraints:												   #
#    														   #
# 1. Use only techniques covered in chapters 1 & 2             #
#    no looping constructs are required.					   #	
################################################################

print "\n          Welcome to Identity Confirmation 2.0\n"; #Intro

print "\nWhat is your name?: ";                             #Prompt

chomp($name = <STDIN>);                    #Store user's name

print "How old are you?  : ";                               #Prompt

chomp($age  = <STDIN>);                    #Store user's age

  $counter  = ($age / 3);                  #Determine counter value

print "\n";

if ($age < 3){
	print "Hello $name. You are $age years old.\n"; # For ages 0,1,2 etc..
} else {
	 while ($counter >= 1){
	 print "Hello $name. You are $age years old.\n";
	 $counter -= 1;
	}
}

print "\n";
