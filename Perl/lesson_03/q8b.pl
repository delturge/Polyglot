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



   %passwords = qw{
			barney llama 
			betty camel 
			wilma llama 
			fred alpaca
		      };

(@passwords_values = (values %passwords));
print "The value list for passwords: @passwords_values\n";


delete $passwords{"fred"};
$passwords{"red"} = "apple";

(@passwords_values = (values %passwords));
print "The value list for passwords: @passwords_values\n";
