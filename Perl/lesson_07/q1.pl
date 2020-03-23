#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 7           #  
# Question 1         #
# 02/18/2007         #
######################
######################
#A menu program.     #
#                    #
#                    #
#                    #
#                    #
######################
use strict;

chomp (my @process_Lines = `ps -aux`);

foreach (@process_Lines) {
	(my @sliced_Process_Lines = split /\s+/, $_);
	print "Owner: $sliced_Process_Lines[0] Command: $sliced_Process_Lines[10]\n";
}


#I thought about splitting up $sliced_Process_Lines[10], but the current output
#is actually more informative. In Red Hat Linux, values [0] and [7] would have
#been printed instead of [0] and [10] in FreeBSD.
