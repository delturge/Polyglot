# In Progress:
                                               Summary
Goal:
=====

The purpose of super_file_processor is to process files with sed -i as safely as possible.

Requirements:
=============

1) Command input must be filtered and validated.
2) Must be able to process directories of text files by mtime, size, or by name (same goes for the files themselves).
3) Signals must be handled to deter write / file altering processes from being interrupted.
4) Logging must be accounted for.
5) No one file should make the process (loops), or system hang.
6) The possibility of a zombie / defunct process should be taken into account.
7) File processing should be "checked" periodically.
8) A time limit for file processing should be established upon command invocation.
9) Processes should be killed gracefully: kill child processes before parent processes.
10) Killing processes via PGID (process group ID) is preferred, if feasible (in progress).
11) Files that cannot be processed must be moved and tracked.
12) Any extra file descriptors opened must be closed before exiting.

Note:
===============

When completed, this program will allow one to edit many files, all while
accounting for system resources, file writing, signals, logging, processes, clean-up,
and the efficient use of I/O.

Using sed once, onr time on a file is unlikely to cause a system resource problem. If you have lots of
files and must process them as a job, the situation changes significantly (memory, I/O, and CPU).
