	chdir "/usr/home/delturge/cochise/cis_248/lesson_06";
	opendir DIR, "./";
	@files = readdir DIR;
	print sort "@files\n";
	closedir DIR;
