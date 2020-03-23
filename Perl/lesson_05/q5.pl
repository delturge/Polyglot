#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 5           #  
# Question  5        #
# 02/07/2007         #
######################
######################
#Algorithm that      #
#gets user input,    #
#compares it to lines#
#in a file, and      #
#prints that  line.  #
######################
use strict;

my $lastname;
my @employee_Data_Lines;
my $employee_Line;
my $search_Flag;



until ($search_Flag > 0){

	print "\n\n\tWelcome to Walmart Lawsuit Blacklister!\n\nEnter Last Name: ";
	chomp ($lastname = <STDIN>);

	open EMPLOYEE_DATA, "<", "telData.txt";
	chomp (@employee_Data_Lines	= <EMPLOYEE_DATA>);
	
	foreach $employee_Line (@employee_Data_Lines){
	   	if ($employee_Line =~ /^($lastname)\|/){
			print "\n$employee_Line\n\n";
			$search_Flag++;
			last;
	   }else{
		    next;   
	   }
	}
}
