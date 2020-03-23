#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 7           #  
# Question 2         #
# 02/18/2007         #
######################
######################
#A menu program.     #
#                    #
#                    #
#                    #
#                    #
######################


#        I really got bogged down in menu item number two on this one.
#      	 No matter how I used loops, index, and substr, I could not
#        get menu item number to to work correctly.




use strict;

&main;

#----------------------------MAIN LOGIC---------------------------------#
sub main { 

        my $sentinel;
	
START:	while ($sentinel == undef){
		   	&Print_Menu;
			$sentinel++
	}
}
#-----------------------------------------------------------------------#

#===========================HELPER FUNCTIONS============================#

sub Clear_Screen {

	if($^O =~ /.*W.*32$/){ 		                  #CLEAR SCREEN FOR WIN32 & UNIX
	   my $clear_String_Windows = `cls`;
	   print $clear_String_Windows;
	}else{
       my $clear_String_Unix = `clear`;
	   print $clear_String_Unix;
    }
	
}

sub Program_Header {                              #PROVIDES CONSISTENT DISPLAY
	my ($input_File, $output_File) = @_;
	print "\n\t\t\t    TEXT PROCESSOR 2.0\n";
	print "\t\t\t    __________________\n\n";
	print "\t\t\t      (Working Files)\n\n\t\t";
    print "Input File: $input_File\t\tOutput File: $output_File\n********************************************************************************";
}

sub Get_INPUT_FILE {                               #GETS AN INPUT FILE

	&Clear_Screen;
	&Program_Header; 

	print "\n\n\nChoose Input File  \n(or ENTER for Menu): ";
	chomp (my $INPUT_FILE = <STDIN>);

   if($INPUT_FILE eq ""){                           #Main Menu
	    &Clear_Screen;
	    next;
   }elsif(! -e $INPUT_FILE){                        #DOES FILE EXIST?
		warn "\n\"$INPUT_FILE\" DOES NOT EXIST!";
		sleep (3);
		next;
   }
	
	my $input_File_Open_Succesful = open INPUT_FILE, "<", "$INPUT_FILE";

 	if (! $input_File_Open_Succesful){                 #WAS FILE OPEN GOOD?
		warn "CANNOT OPEN \"$INPUT_FILE\"";
		sleep (3);
		next;
	}
	
    if (-T $INPUT_FILE){                               #IS THIS A TEXT FILE?
        my $line_Counter;
	   
	    foreach (<INPUT_FILE>){			               #ONE OR MORE LINES?
			$line_Counter++;
			if ($line_Counter >= 1){
				last;				
		   }else{
			    warn "\n\"$INPUT_FILE\" HAS NO LINES OF TEXT!";
				sleep (3);
				next;			   
		   }
		}				                               #NOT A TEXT FILE
   }else{
        warn "\n\"$INPUT_FILE\" IS NOT A TEXT FILE!";
		sleep (3);
		next;
   }

    close INPUT_FILE;
	$INPUT_FILE;   
}


sub Get_OUTPUT_FILE {                            #GETS AND OUTPUT FILE
	
	print "\n\nChoose Output File\n(or ENTER for Menu): ";
	chomp (my $OUTPUT_FILE = <STDIN>);

    if($OUTPUT_FILE eq ""){                      #END PROGRAM
	    &Clear_Screen;
	    next;
    }elsif (! -e $OUTPUT_FILE){                  #DOES FILE EXIST?
		warn "\n\"$OUTPUT_FILE\" DOES NOT EXIST!";
		sleep (3);
		next;
	}
	
	my $output_File_Open_Succesful = open OUTPUT_FILE, ">", "$OUTPUT_FILE";

 	if (! $output_File_Open_Succesful){               #WAS FILE OPEN GOOD?
		warn "CANNOT OPEN \"$OUTPUT_FILE\"";
		sleep (3);
		next;
	}
	
	close OUTPUT_FILE;
	&Clear_Screen;
	$OUTPUT_FILE;
	
}


sub Verify_INPUT_and_OUTPUT_Files {            #IDENTIFY INPUT AND OUTPUT
  
    my ($INPUT_FILE, $OUTPUT_FILE) = (&Get_INPUT_FILE, &Get_OUTPUT_FILE);	

}

#==============================================================================#


#++++++++++++++++++++++++++++++MENU FUNCTIONS++++++++++++++++++++++++++++++#

sub Print_Menu {                                 #What you see when at start.

	my $user_Selection;
	while ($user_Selection != 6){
        &Clear_Screen;
		&Program_Header;
		print "\n\n
1. Replace Word\n\n 
2. Replace String\n\n
3. Sort Lines\n\n
4. Count Words\n\n
5. Quit\n\n\n";
	
		print "Choose Task (1-6), then \"ENTER\": \n===============================: ";
		chomp ($user_Selection = <STDIN>);
	
		if    ($user_Selection == 1){ #Replace Word
			   &Replace_Word(&Verify_INPUT_and_OUTPUT_Files);
	   }elsif ($user_Selection == 2){ #Replace String
		       &Replace_String(&Verify_INPUT_and_OUTPUT_Files);
	   }elsif ($user_Selection == 3){ #Sort Lines
		       &Sort_Lines(&Verify_INPUT_and_OUTPUT_Files);
	   }elsif ($user_Selection == 4){ #Count Words
		       &Count_Words(&Get_INPUT_FILE);
	   }elsif ($user_Selection == 5){ #Quit
			   &Clear_Screen;
			   last START;		   
	   }else{
			warn "********* Invalid Selection *********";
			sleep (2);		   
	   }
   }
	
}


sub Replace_Word {                               #Menu Option #1
	my ($input_File, $output_File) = @_;

	&Program_Header($input_File, $output_File);
	print "\n\n\t\t\t       REPLACE WORD\n\t\t\t       ************";

	print "\n\n\nType Word To Change    \n(or ENTER for Menu): ";
	chomp (my $target = <STDIN>);

	if($target eq ""){                           #Go to Main Menu
	    next;
   }

	print "\n\n\nSpecify Replacement    \n(or ENTER for Menu): ";
	chomp (my $replacement = <STDIN>);

   if($replacement eq ""){                       #Go to Main Menu
	   next;
    }

    open INPUT_FILE,  "<", "$input_File";
	open OUTPUT_FILE, ">", "$output_File";

	while (<INPUT_FILE>){                        #Search, replace, print
		chomp;
		s/\s*(?:$target)\s*/$replacement/g;
		print OUTPUT_FILE "$_\n";
	}
}


sub Replace_String {                             #Menu Option #2
	my ($input_File, $output_File) = @_;

	&Program_Header($input_File, $output_File);
	print "\n\n\t\t\t       REPLACE STRING\n\t\t\t       **************";

	my($target, $replacement);

	print "\n\n\nType String To Change    \n(or ENTER for Menu): ";
	chomp ($target = <STDIN>);

	if($target eq ""){                           #Go to Main Menu
	    next;
   }

	print "\n\n\nSpecify Replacement    \n(or ENTER for Menu): ";
	chomp ($replacement = <STDIN>);

   if($replacement eq ""){                       #Go to Main Menu
	   next;
    }

	open INPUT_FILE,  "<", "$input_File";
	open OUTPUT_FILE, ">", "$output_File";


	my $position = 0;
	my $location;
    my $line;
	
	while(<INPUT_FILE>){                      #Search, replace, print
		chomp ($line = $_);
		my @line_Word = split /\s+/, $line;
	    my $position = 0;
		my $location;
		foreach (@line_Word){
				$location = index($_, $target, $position);
    			substr($_, $location) = $replacement;	
	    }	
		$line = join " ", @line_Word;
		print OUTPUT_FILE "$line\n";			 
    }
}


sub Sort_Lines {                                  #Menu Option #3
	my ($input_File, $output_File) = @_;
	
	&Program_Header($input_File, $output_File);
	print "\n\n\t\t\t       SORT LINES\n\t\t\t       **********";

	print "\n\n\nDo you really want to sort \"$input_File\" into \"$output_File\" (y/n)?   \n(or ENTER for Menu): ";
	chomp (my $response = <STDIN>);

	if($response eq "" || $response eq "n"){      #Go to Main Menu
	    next;
   }elsif ($response eq "y"){                     #Perfomr Sort
    	open INPUT_FILE,  "<", "$input_File";
		open OUTPUT_FILE, ">", "$output_File";
		
	    my $unsorted_Line_Counter = 0;
		my @unsorted_Lines;
	   
		while(<INPUT_FILE>){                      #Put lines in an array
    		chomp ($unsorted_Lines[$unsorted_Line_Counter++] = $_);
		}
	
	    (my @sorted_Lines = sort @unsorted_Lines);

		foreach my $line (@sorted_Lines){         #Prnit sorted lines
	 		print OUTPUT_FILE "$line\n";
		}
			
   }else{
		warn "\nInvalid Selection";
		sleep (3);
    	next;	   
   }
		 
}


sub Count_Words {                                 #Menu Option #4
	my ($input_File) = @_;
	&Clear_Screen;
	&Program_Header($input_File);
	print "\n\n\t\t\t       COUNT WORDS\n\t\t\t       ***********";

	
	my $total_Words = 0;
	my $words_Per_Line = 0;
	my @split_Line;
	
	open INPUT_FILE,  "<", "$input_File";	
	foreach (<INPUT_FILE>){
		@split_Line = split /\s+/, $_;
		$words_Per_Line = @split_Line;
		$total_Words += $words_Per_Line;		
	}
	
	print "\n\n\nThere are $total_Words words in \"$input_File\".   \n(hit ENTER for Main Menu): ";
	chomp (my $response = <STDIN>);
	
	if($response eq ""){                          #Go to Main Menu
	    next;
    }
	
}

#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++#
