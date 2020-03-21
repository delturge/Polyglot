#!/bin/bash

# Note: Totally raw and unfinished, but close. Needs some refactoring.
# Always read shell scripts the bottom to the top.

##################################################################
#                        Handle Signals                          #
##################################################################

# Close file descriptor 2 upon script exit.
trap 'exec 2>/dev/stderr; exit 1' 0

##################################################################
#                  Open File Descriptors                         #
##################################################################

# Assign file descriptor 2 to the application log: /var/log/animals/error.log
exec 2>/var/log/animals/error.log 

##################################################################
#                    Load the libraries                          #
##################################################################

. ../library/Base/Base.sh
. ../classes/SignalHandler.sh
. ../classes/FileProcessorLogger

################################################################################
################################################################################
################################################################################


################### APPLICATION HELPER FUNCTIONS #################


##################################################################
#            Functions that process each type of file.           #
##################################################################

##
# Makes an edit to all file types.
# Changes blah, to foo bar.
#
# @param string $1 Name of the file to edit.
# @return bool
###
function generalEdit001 ()
{
    if sed -i -n -r 's/s/r/g' "$filename"                     
    then                                                                         
        return 0        
    else                                                                   
        return 1                                        
    fi       
}

##
# The 1st edit for dog files.
# Changes blah, to foo bar.
#
# @param string $1 Name of the file to edit.
# @return bool
###
function cashEdit001 ()
{
    if sed -i -n -r 's/a/e/g' "$filename"                     
    then                                                                         
        return 0        
    else                                                                   
        return 1                                        
    fi       
}

##
# The 2nd edit for dog files.
# Changes blah, to foo bar.
#
# @param string $1 Name of the file to edit.
# @return bool
###
function cashEdit002 ()
{
    if sed -i -n -r 's/i/o/g' "$filename"                     
    then                                                                         
        return 0        
    else                                                                   
        return 1                                        
    fi       
}

##
# The 3rd edit for dog files.
# Changes blah, to foo bar.
#
# @param string $1 Name of the file to edit.
# @return bool
###
function cashEdit003 ()
{
    if sed -i -n -r 's/u/y/g' "$filename"                     
    then                                                                         
        return 0        
    else                                                                   
        return 1                                        
    fi       
}

function processCash ()
{
    declare -ir GOOD_TRANSACTION=4
    declare -i numEdits=0

    trap '' INT QUIT HUP ILL ABRT EMT BUS FPE SEGV PIPE TERM

    declare -r INPUT_FILE="$1"
    declare -r OUTPUT_FILE="$2"

    if generalEdit001 "$filename"
    then                                            
        (( numEdits++ ))
    else
        return 1                  
    fi

    if cashEdit001 "$filename"
    then                                                  
        (( numEdits++ ))
    else
        return 2
    fi

    if dogEdit002 "$filename"
    then                                                   
        (( numEdits++ ))
    else
        return 3
    fi

    if dogEdit003 "$filename"
    then                                        
        (( numEdits++ ))
    else
        return 4
    fi

    trap - INT QUIT HUP ILL ABRT EMT BUS FPE SEGV PIPE TERM
}

function processCredit ()
{
    :
}

function processDebit ()
{
    :
}

function processTrade ()
{
    :
}

function processRefund ()
{
    :
}

##################################################################
#                      Main Application Logic                    #
##################################################################

##
# Check the file processing status of a process that has exceeded
# its processing time, or processing checks.
#
# @author Anthony E. Rutledge
# @version 1.0
# @copyright (c) 2020, Anthony E. Rutledge
#
# @param string $1 The parent process ID
# @param string $2 The process ID
# @param string $3 The absolute path name of the file being processed.
#
# @return bool Returns 0 if all files were processed. Otherwize, non-zero is returned.
###
function checkFileProcessingStatus ()
{
    declare -r PPID=$1
    declare -r PID=$2
    declare -r FILENAME="$3"
    declare -r BAD_MESSAGE="Process ${PID} of parent ${PPID} is taking too long to process ${FILENAME}!"
    declare -r GOOD_MESSAGE="Process ${PID} of parent ${PPID} finished processing file ${FILENAME}. All good."

    if isProcess $lastJobPid
    then
        logToApp "warning" $BAD_MESSAGE
        # Send alert or message to admin.
        return 0
    else
        logToApp "info" $GOOD_MESSAGE
    fi
    
    return 1
}

##
# Kill a CPU process that has taken too long.
#
# @author Anthony E. Rutledge
# @version 1.0
# @copyright (c) 2020, Anthony E. Rutledge
#
# @param string $1 The parent process ID
# @param string $2 The process ID
# @param string $3 The absolute path name of the file being processed.
#
# @return bool Returns 0 if all files were processed. Otherwize, non-zero is returned.
###
function stopProcessingFile ()
{
    declare -r PPID=$1
    declare -r PID=$2
    declare -r FILENAME="$3"
    declare -r GOOD_MESSAGE="Intentionally killed child process ${PID} of parent ${PPID} while processing ${FILENAME}!"
    declare -r BAD_MESSAGE="Unable to kill child process ${PID} of parent ${PPID}. May still be processing ${FILENAME}!"

    if killPidFamily $PID
    then
        logToApp "notice" $GOOD_MESSAGE
    else
        logToApp "alert" $BAD_MESSAGE
        # Send alert or message to admin.
    fi
}

##
# Move a file that has taken to long to process to the
# "blah/blah/blah/errors/${fileTypeDir}/" directory.
#
# @author Anthony E. Rutledge
# @version 1.0
# @copyright (c) 2020, Anthony E. Rutledge
#
# @param string $1 The parent process ID
# @param string $2 The process ID
# @param string $3 The absolute path name of the file being processed.
#
# @return bool Returns 0 if all files were processed. Otherwize, non-zero is returned.
###
function moveBadFile ()
{
    declare -r PPID=$1
    declare -r PID=$2
    declare -r FILENAME="$3"
    declare -r GOOD_MESSAGE="Notice: Moved file ${filename} to its error directory! PID=${PID} PPID=${PPID}"
    declare -r BAD_MESSAGE="Alert: Unable to move ${filename} to its error directory! PID=${PID} PPID=${PPID}"

    if mv -f $FILENAME $ERROR_DIR
    then
        logToApp "notice" $PPID $PID $FILENAME $GOOD_MESSAGE
    else
        logToApp "alert" $BAD_MESSAGE
        # Send alert or message admin.
    fi
}

##
# Processes all the files in a directory with single for loop,
# all while limiting processing time.
#
# @author Anthony E. Rutledge
# @version 1.0
# @copyright (c) 2020, Anthony E. Rutledge
#
# @param string $1 The name of the file processing function to execute.
# @param int    $2 The maximum seconds to process a file.
# @param int    $3 The maximum times to check on the file processing status.
# @param int    $4 The seconds between file processing checks.
# @param string $5 The directory location of the files. Example: "/opt/files/" <--- Yes, add trailing slash.
#
# @return bool Returns 0 if all files were processed. Otherwize, non-zero is returned.
###
function processFiles ()
{
    trap 'cleanUp $CURRENT_PID $lastJobPid $BASH_COMMAND $LINENO; exit' HUP INT QUIT ILL BUS ABRT SEGV PIPE TERM

    declare -r CURRENT_PID=$$
    
    declare -r FILE_PROCESSING_FUNCTION="$1"
    declare -ir MAX_PROCESSING_SECONDS=$2
    declare -ir MAX_PROCESS_CHECKS=$3
    declare -ir MAX_DELAY_SECONDS=$4
    declare -r TARGET_DIR="$5"
    declare -r FINISHED_DIR="$6"
    declare -r ERROR_DIR="$7"

    declare absoluteFilePath
    declare lastJobPid

    # You must turn on null globbing to account for the empty directory edge case
    # while using * to loop through the contents of a directory with a for loop.
    # Otherwise, * itself will be looped over as a literal "*".

    # Check to see if null globing is already set.
    if ! shopt -q nullglob
    then
        # Allows the * to resolve to filenames only in a for loop.
        shopt -s nullglob
    fi

    # Where the files to be processed are located.
    cd "$TARGET_DIR"

    # Iterate over all files in the $TARGET_DIRECTORY
    for filename in *
    do
        absoluteFilePath="${TARGET_DIR}${filename}"
 
        # Process the file in the background.
        $FILE_PROCESSING_FUNCTION "$filename" &
        lastJobPid=$!

        # Monitor file processing in the foreground.
        if limitProcessRuntime $lastJobPid $MAX_PROCESSING_SECONDS $MAX_PROCESS_CHECKS $MAX_DELAY_SECONDS
        then
            mv -f $filename $FINISHED_DIR
        else
            if checkFileProcessingStatus $CURRENT_PROCESS_ID $lastJobPid $absoluteFilePath
            then
                stopProcessingFile $CURRENT_PID $lastJobPid $absoluteFilePath
                moveBadFile $CURRENT_PID $lastJobPid $absoluteFilePath
            fi
        fi
    done
}

##
# @author Anthony E. Rutledge
# @version 1.0
# @copyright (c) 2020, Anthony E. Rutledge
#
# Processes several directories of files quasi-transactionally with sed.
#
# 1) Processes each file in the background.
# 
# 2) Limits the processing time of each file processing process with a
#    monitoring process.
# 
# 3) Traps process killing signals (HUP, INT, QUIT, TERM, ...) to discourage
#    incomplete file writes.
#
# 4) Logs errors to
#       a) Application log
#       b) System log
#
# 5) Able to send e-mail to notify about exceptional situations.
#
# 6) Maintains per-directory transaction manifest:
#
# 7) Cleans-up the environment before the process dies, unless SIGKILL (9) is issued.
#
# @param string $1 The directory location of where to start from.
# @param string $2 The order to process customer directories in: mtime, size, or by name.
# @param string $3 The order to process files in customer directories: mtime, size, or by name.
# @param int    $4 The maximim number of files to process, consecutively, in a customer's directory in one pass.
# @param int    $5 The maximim seconds allowed to process a file.directory location of the files. Example: "/opt/files/" <--- Yes, trailing slash.
# @param int    $6 The maximum process checks allowed before the process gets killed.
# @param int    $7 The maximum amound of time, in seconds, between process checks.
#
# @return bool Returns 0 if all files were processed. Otherwise, non-zero is returned.
###
function main ()
{
# TODO: Move constants, variables, and filtering and validation of user input from global area to here.
# TODO: Move main's current logic into a function.
# TODO: Let main() call this new function to do the work of iterating over directories (which is what main() does now).

    declare -r CURRENT_PID=$$
    declare -r SORTED_FILES_DIR="sorted/"
    
    # Used to format the output of the /usr/bin/time command.
    declare -r TIME_FORMAT="CPUKernel:%S CPUUser:%U CPUTotal:%P ExecTime:%E ExecSecs:%e MaxMemKB:%M AveResMemKB:%t AveTotalMemBK:%K FilesIn:%I FilesOut:%O SignalsIn:%k"

    declare -r ROOT_INPUT_DIR=$1
    declare -r DIR_SORT_KEY=$2
    declare -r DIR_SORT_ORDER=$3
    declare -r FILE_SORT_KEY=$4
    declare -r FILE_SORT_ORDER=$5
    declare -r MAX_FILES_PER_DIR=$6
    declare -ir MAX_PROCESSING_SECONDS=$7
    declare -ir MAX_PROCESS_CHECKS=$8
    declare -ir MAX_DELAY_SECONDS=$9

    # The absolute path to the sorted file directories.
    declare -r TARGET_ROOT_PATH="${ROOT_INPUT_DIR}${SORTED_FILES_DIR}"
    declare targetDir

    # Types of files to process. One directory per file type.
    declare -Ar TARGET_DIRS=($(ls -ld "${TARGET_ROOT_PATH}"*/))

    # The number of directories to process.
    declare -ir TARGET_DIRS_LENGTH=${#DIRECTORIES[@]}

    # Directories where all the files did not process successfully.
    declare -A errorDirs=()

    # The number of successfully processed directories.
    declare -i processedDirs=0

    # Iterate through all directories. 
    for fileTypeDir in "${TARGET_DIRS[@]}"
    do
        fileProcessingFunction="process${fileTypeDir}" # Build the name of the directory processing function.
        targetDir="${TARGET_ROOT_PATH}${fileTypeDir}"

        # Add log entry header.
        cat <<- EOF 1>>&2
        ==========
        JOB START: 
        $(getDateTime) "$targetDir" $(hostname) $(hostname -i | awk '{print $2}') # Date Directory hostname IP
        $(getProcessReport $CURRENT_PID)
        ----------
        EOF

        # The /usr/bin/time command will add the log body.
        # processFiles () will iterate through the files of a directory.
        
        if /usr/bin/time -f $TIME_FORMAT processFiles \
            "$fileProcessingFunction" $MAX_PROCESSING_SECONDS $MAX_PROCESS_CHECKS $MAX_DELAY_SECONDS "$targetDir"
        then
            # The file set was processed successfully.
            (( processedDirs++ ))
        else
            errorDirs[$fileTypeDir]=${TARGET_DIRECTORIES["$creature"]}
            errorMessage "All $creature files were not processed!"
        fi

        # Add log entry footer.
        cat <<- EOF 1>>&2
        $(getDateTime) $targetDir $(hostname) $(hostname -i | awk '{print $2}')
        $(getProcessReport $CURRENT_PID)
        JOB END: 
        ==========
        EOF
    done

    if (( processedDirs == DIRECTORIES_LENGTH ))
    then
        message "Processing complete! All files in all directories were processed."
        return 0
    else
        errorMessage "Animal files moved to a new directory."
    fi

    return 1
}

################################################################################

## In progress: Refactorization of global area (to end of file) on-going: 3/20/2020

###################################################################
###############              CONSTANTS                #############
#############################(Limits)##############################
# TODO: Move constants to main(), if possible.

# The minimum files to process at a time.
declare -ir MIN_FILES_PER_CUSTOMER_JOB=1

# The maximum files to process in one customer directory (..../input/sorted/<customer>) at a time.
declare -ir MAX_FILES_PER_CUSTOMER_JOB=1000

#----------

# The minimum time allowed to process a file.
declare -ir MIN_FILE_PROCESSING_SECONDS=1

# The maximum time allowed to process a file.
declare -ir MAX_FILE_PROCESSING_SECONDS=30

#----------

# The minimum times a process check might occur per file processed.
declare -ir MIN_PROCESS_CHECKS=10

# The maximum times a process check can occur per file processed.
declare -ir MAX_PROCESS_CHECKS=20

#----------

# The minimum possible delay between process checks.
declare -ir MIN_DELAY_SECONDS=1

# The maximum possible delay between process checks.
declare -ir MAX_DELAY_SECONDS=5

###################################################################
############### Variables for Command Line Arugments ##############
########################### (Defaults) ############################
# TODO: Move variables to main(), if possible.

# The uppermost parent directory for the entire taks to be done.
declare rootInputDir="/var/local/yourApp/data/input/"

# The order to process the customer directories under ..../input/sorted/
declare dirOrder="mtime"

# The order to process the customer files under ..../input/sorted/<customer>)
declare fileOrder="mtime"

# The maximum number of files to process in a customer folder at a time.
declare maxFilesPerDir=100

# The maximum number of seconds to attempt processing a file.
declare maxFileProcessingSeconds=15

# The maximum number of times allowed to check to see if process has finished.
declare maxProcessChecks=10

# The delay in seconds between process checks.
declare maxDelaySeconds=1

###################################################################
###################################################################
###################################################################

################################################################################
#                         Command Line Option Lengend
#
# -d = The immediate parent directory (../) of all directories to process: "/var/local/<application>/data/input/"
# -O = Order of directory processing: file name (default), oldest, newest, smallest, largest
# -o = Order of file processing: file name (default), oldest, newest, smallest, largest
# -q = maxFilesPerDir The max number of files to process in any one set: Default = 100
# -s = maxFileProcessingSeconds for each file: Default = 15
# -c = maxProcessChecks during the processing of a file: Default = 10
# -w = maxDelaySeconds between process checks: Default = 1
#
################################################################################
# TODO: Clean up getops algorithm. Add filter step before assigning the value of $OPTARG

while getopts :d:t:o:S:C:D: option
do
    case "$option" in:
        d) rootInputDir=$OPTARG
        O) dirOrder=$OPTARG
        o) fileOrder=$OPTARG
        q) maxFilesPerDir=$OPTARG
        s) maxFileProcessingSeconds=$OPTORG
        c) maxProcessChecks=$OPTORG
        w) maxDelaySeconds=$OPTORG
        :) echo "Invalid argument to $option"
       \?) echo "Invaild option."
    esac
done

################### Validate User Supplied Options & Arguments #################
# TODO: Move validation steps (if possible) to main()

if [[ ! isGoodRootInputDir $rootInputDir ]]
then
    exit 1
fi

if [[ ! isGoodDirOrder $dirOrder ]]
then
    exit 2
fi

if [[ ! isGoodFileOrder $fileOrder ]]
then
    exit 3
fi

if [[ ! isGoodMaxFilesPerDir $MIN_FILES_PER_CUSTOMER_DIR $MAX_FILES_PER_CUSTOMER_DIR $maxFilesPerDir ]]
then
    exit 4
fi

if [[ ! isGoodMaxFileProcessingSeconds $MIN_FILE_PROCESSING_SECONDS $MAX_FILE_PROCESSING_SECONDS $maxFileProcessingSeconds ]]
then
    exit 5
fi

if [[ ! isGoodMaxProcessChecks $MIN_PROCESS_CHECKS $MAX_PROCESS_CHECKS $maxProcessChecks ]]
then
    exit 6
fi

if [[ ! isGoodMaxDelaySeconds $MIN_DELAY_SECONDS $MAX_DELAY_SECONDS $maxDelaySeconds ]]
then
    exit 7
fi

################################################################################

main "$rootInputDir" "$dirOrder" "$fileOrder" $maxFilesPerDir $maxFileProcessingSeconds $maxProcessChecks $maxDelaySeconds
