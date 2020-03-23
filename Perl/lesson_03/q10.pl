#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 3           #  
# Question10         #
# 01/26/2007         #
######################
######################
#Algorithm that      #
#takes in files at   #
#a prompt. Counts the#
#total number of     #
#line and prints them#
######################
use strict;



&line_count_print;                     #just wanted to practice


#------------------------------------------------------------------------------#

sub line_count_print {

   my $number_of_lines;                    
   my @array_of_lines;

   while (<>){                          #process all arguments
       chomp;                           #remove new line
       push (@array_of_lines, "$_");    #One at a time, put into @array_of_lines
   }

   print "Total lines in files: ", ($number_of_lines = @array_of_lines), ".\n";

   foreach (@array_of_lines){
       print "$_\n";
   }
}
