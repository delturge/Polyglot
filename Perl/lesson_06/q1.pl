#!/usr/bin/perl
######################
# Anthony E. Rutledge#
# Lesson 6           #  
# Question  1        #
# 02/18/2007         #
######################
######################
#Algorithm for user  #
#additions to the    #
#password file.      #
#                    #
#                    #
######################
use strict;

&add_New_User_Data(&process_New_User_Data(&get_New_User_Data));



sub get_New_User_Data {
    my $clear_screen = `clear`;

	my $password_File = "passwd.txt";
	my $password_File_Handle_Open_Works;

	my $data_counter = 0;
	my @new_User_Data;

	if ( -e $password_File && -w $password_File){
		$password_File_Handle_Open_Works = open PASSWORD_FILE, "<", "$password_File" ;
   }else{
	    die "Password file is inaccessible!";
   }
   
	if ($password_File_Handle_Open_Works){
		print $clear_screen;
		print "\n\tWelcome to User Adder!\n\nPlease provide the following information."; 
		
		print "\n\n\nUSER ID: " ;
		chomp ($new_User_Data[$data_counter] = <STDIN>);

	    $data_counter++;
		print "\n\nPASSWORD: ";
		chomp ($new_User_Data[$data_counter] = <STDIN>);

		$data_counter++;
		print "\n\nUSER NUMBER: ";
		chomp ($new_User_Data[$data_counter] = <STDIN>);

		$data_counter++;
		print "\n\nREAL NAME: ";
		chomp ($new_User_Data[$data_counter] = <STDIN>);		
   }else{
	    die "Program has suffered fatal error!";
	}
	
	@new_User_Data;
}

sub process_New_User_Data {
	my ($user_Id, $password, $user_Number, $real_Name) = @_;

	my $user_Line_sentinel;
	my $target_Line;
	
	while (<PASSWORD_FILE>){ 
		$user_Line_sentinel = $_ ;
		if ($user_Line_sentinel =~ /^grs:/){
			chomp($target_Line = "($`)($&)$'");  # I did this just to see if it would work.
			last;
	    }
	}
	
	my @sliced_Target = split /:/, $target_Line;
	
	my ($group_Number, $home_Directory, $shell) = ($sliced_Target[3],$sliced_Target[5], $sliced_Target[6]);
	
	my @mixed_Fields = ($user_Id, $password, $user_Number, $group_Number, $real_Name, $home_Directory, $shell);
	
	my $merged_line = join ":", @mixed_Fields;
}

sub add_New_User_Data {
	my $new_User = $_[0];
	my $password_File = "passwd.txt";
	my $password_File_Handle_Open_Works;

	if ( -e $password_File && -w $password_File){
		$password_File_Handle_Open_Works = open PASSWORD_FILE, ">>", "$password_File" ;
   }else{
	    die "Password file is inaccessible!";
   }
   
	if ($password_File_Handle_Open_Works){
		open PASSWORD_FILE, ">>", "$password_File";
		print PASSWORD_FILE "$new_User\n";
		print "\n\tUser Add Complete!\n\n";	
   }else{
	    die "Program has suffered fatal error!";
	}
	
}
