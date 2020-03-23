#!/usr/bin/perl
#testing answer

@test = qw (this is a test of);

($a, $b, $c) = (cat, dog, fish);

print "\n$a, $b, $c\n" ;

print $test[2*2];

chomp ($c);

print "\n$c\n";

$retest = join "|", @test;

print "\n$retest";
