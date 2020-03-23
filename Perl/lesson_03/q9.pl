#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 3           #  
# Question 9         #
# 01/26/2007         #
######################
######################
#Algorithm that      #
#compares user input #
#strings to keys of a#
#hash.               #
#                    #
######################



%passwords = (                       #Just trying this format out.
   "barney" => "llama",
    "betty" => "camel", 
	"wilma" => "llama", 
	"fred"  => "alpaca",
);

@passwords_keys = keys (%passwords);


$match;
$check;

chomp($match = <STDIN>);

while ( $match ne $check && $i < ($limit = keys (%passwords))){
    $check = pop (@passwords_keys);
    $i++;
}

######################THE PART YOU ARE LOOKING FOR#############################
 if ($match eq $check){
 	print "Key found.\n";	 
}else{ 
	print "Key not found.\n"; 
}	
###############################################################################	

 
 #I'm not really sure if this is what you want.
 #However, this is a conditional statement. Running
 #this produces a one time find.
