#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 6           #  
# Question 7         #
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
use Cwd;
 
 
&main;
 
sub main { 
	
	&Clear_Screen;	
    my $user_Selection;
	while ($user_Selection != 7){

		&Clear_Screen;	
				
		print "\n\t\t\tFILE SYSTEM MANIPULATOR 5.0\n";
		print "\t\t\t____________________________\n\n";
		print "\t\t\t     (Current Directory)\n\n\t\t";

		&Show_Current_Directory;
	
		print "\n\n
1. Change Current Directory\n\n 
2. List Files\n\n
3. Remove Files\n\n
4. Rename Files\n\n
5. Create Directory\n\n
6. Change File Permissions\n\n
7. Exit\n\n";
	
		print "Choose Task (1-7), then \"ENTER\": ";
		chomp ($user_Selection = <STDIN>);
	
		if ($user_Selection == 1){ 
			&Change_Directory;
	   }elsif ($user_Selection == 2){
		    &List_Files;
	   }elsif ($user_Selection == 3){ 
		    &Remove_Files;
	   }elsif ($user_Selection == 4){ 
		    &Rename_Files;
	   }elsif ($user_Selection == 5){
		    &Create_Directory;
	   }elsif ($user_Selection == 6){
		    &Change_Permissions;
	   }elsif ($user_Selection == 7){
			&Clear_Screen;	
	   }else{
			warn "********* Invalid Selection *********";
			sleep (2);		   
	   }
	}
}


sub Clear_Screen {

	if($^O =~ /.*W.*32$/){ 		
	   my $clear_String_Windows = `cls`;
	   print $clear_String_Windows;
	}else{
       my $clear_String_Unix = `clear`;
	   print $clear_String_Unix;
    }
	
}


sub Show_Current_Directory {
      my $current_Directory = getcwd;
	  print $current_Directory;
}


sub Quick_File_List {

	print"\n\n\nDirectory Contains.\n___________________\n";

	my $current_Directory = ".";
	opendir DIRECTORY, "$current_Directory";
	my @files = readdir DIRECTORY;
	my @sorted_Files = sort @files;
		
	foreach my $file (@sorted_Files){
		print "$file\n";
	}
	
}


sub Change_Directory {
	my $path;
	while($path ne " "){
		&Clear_Screen;

		print "\t\t\t\tCHANGE DIRECTORY\n";
		print "\t\t\t\t________________ \n\n";
		print "\t\t\t       (Current Directory)\n\n\t\t";

		&Show_Current_Directory;
		
		print "\n\n\n\n\n\n Enter Desired Path \n(SPACE BAR for Main Menu)\n========================: ";

		chomp ($path = <STDIN>);
		
		if($path eq " "){
		   last;
		}elsif (-e $path){
			chdir $path; 
			next;
	    }else{
			warn "\n\n $path NOT FOUND\n\n";
			sleep (2);
		}
		
		
	}
}


sub List_Files {
	my $sentinel;
	while ($sentinel ne " "){
		&Clear_Screen;
	
		print "\t\t\t\t LIST FILES\n";
		print "\t\t\t\t __________ \n\n";
		print "\t\t\t     (Current Directory)\n\n\t\t";

		&Show_Current_Directory;		
		&Quick_File_List;
		
		print "\n\n Enter SPACE BAR for Main Menu: \n =============================: ";
		chomp ($sentinel = <STDIN>);
	}
}


sub Remove_Files {
	my $file;
	while($file ne " "){
		&Clear_Screen;
	
		print "\t\t\t\t REMOVE FILES\n";
		print "\t\t\t\t ____________ \n\n";
		print "\t\t\t      (Current Directory)\n\n\t\t";

		&Show_Current_Directory;
		&Quick_File_List;
		
		print "\n\n Type file name to remove\n (or SPACE BAR for Main Menu).\n ============================: ";

		chomp (my $file = <STDIN>);
		if ($file eq " "){
			last;
		}elsif (! -e $file){
			warn "\n\"$file\" DOES NOT EXIST!";
			sleep (3);			
		}else{
			unlink "$file";
		}
	}
}


sub Rename_Files {
	my $file;
	my $new_File_Name;
	while ($file ne " " || $new_File_Name ne " "){
	&Clear_Screen;
	
	print "\t\t\t\t RENAME FILES\n";
	print "\t\t\t\t ____________ \n\n";
	print "\t\t\t      (Current Directory)\n\n\t\t";

	&Show_Current_Directory;
	&Quick_File_List;
		
	print "\n\n Type file to rename\n (or SPACE BAR for Main Menu).\n ============================: ";
	chomp ($file = <STDIN>);

	print "\n\n Type new name\n (or SPACE BAR for Main Menu).\n ============================: ";
	chomp ($new_File_Name = <STDIN>);
		
	if ($file eq " " || $new_File_Name eq " "){
		last;
    }elsif (! -e $file){
		warn "\n\"$file\" DOES NOT EXIST!";
		sleep (3);			
    }else{
	    rename $file, $new_File_Name;
		}
	}
}


sub Create_Directory {
	my $new_Directory;
	my $permissions;
	my $successful_Directory_Creation;
	while (){
		&Clear_Screen;
	
		print "\t\t\t\t CREATE DIRECTORY\n";
		print "\t\t\t\t ________________ \n\n";
		print "\t\t\t        (Current Directory)\n\n\t\t";

		&Show_Current_Directory;
		&Quick_File_List;
		
		print "\n\n Type New Directory Name?\n (or SPACE BAR for Main Menu).\n ============================: ";
		chomp ($new_Directory = <STDIN>);

		if ($new_Directory eq " "){
			last;
		}
		
	    print "\n\n Specifify Permissions (oct). \n (or SPACE BAR for Main Menu).\n ============================: ";
		chomp ($permissions = <STDIN>);
		
	
		if ($permissions eq " "){
			last;
		}elsif (-e $new_Directory){
			warn "\n\"$new_Directory\" ALREADY EXIST!";
			sleep (3);			
		}else{
			$successful_Directory_Creation = mkdir $new_Directory, oct($permissions)
		}

		
		if (! $successful_Directory_Creation){
			warn "Directory creation FAILED!";
			sleep (3);
		}
		
	}
}


sub Change_Permissions {
	my $file;
	my $permissions;
	my $successful_Permissions_Change;
	
	while(){
		&Clear_Screen;
	
	print "\t\t\t\t CHANGE PERMISSIONS\n";
	print "\t\t\t\t __________________ \n\n";
	print "\t\t\t         (Current Directory)\n\n\t\t";

	&Show_Current_Directory;
	&Quick_File_List;
	
		print "\n\n Type The File Name?\n (or SPACE BAR for Main Menu).\n ============================: ";
		chomp ($file = <STDIN>);

		if ($file eq " "){
			last;
		}
		
	    print "\n\n Specifify Permissions (oct). \n (or SPACE BAR for Main Menu).\n ============================: ";
		chomp ($permissions = <STDIN>);
		
	
		if ($permissions eq " "){
			last;
		}elsif (! -e $file){
			warn "\n\"$file\" DOES NOT EXIST!";
			sleep (2);			
		}else{
			$successful_Permissions_Change = chmod oct($permissions), $file ;
		}

		
		if (! $successful_Permissions_Change){
			warn "Permission change FAILED!";
			sleep (2);
		}else{
			print "Permissions on \"$file\" CHANGED";
			sleep (2);
		}
	}
}
