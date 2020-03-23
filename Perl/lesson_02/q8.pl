#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 2           #  
# Question 8         #
# 01/19/2007         #
# 01/19/2007         #
######################
######################
# Algorithm that gets#
# strings and prints #
# them sorted and    #
# reversed.          #
#                    #
######################
######################

print "\n                Welcome to List Sorter!\n\n\n";
print "Hit enter after each item.\n(Hit Enter By Itself To Quit): \n\n";      #Prompt

chomp ($sentinel = <STDIN>);                  #Priming the loop.           

while ($sentinel ne "") {                     #Gather legitimate elements
    push (@unsortedList, $sentinel);
    chomp ($sentinel = <STDIN>);
}

@sortedList = sort @unsortedList;           #Sort and store

print "@sorted_List\n\n";	
	
@reversed_List = reverse @sorted_List;         #Reverse and store

print "@reversed_List\n\n";
