#!/bin/bash

# Note: Totally raw and unfinished, but close. Needs some refactoring.
# Always read shell scripts the bottom to the top.

##################################################################
#                     Setup Signal Handling                      #
##################################################################

# Set stderr back to file descriptor number two upon exit.
trap 'exec 2>/dev/stderr; exit 1' 0

##################################################################
#                  Open Error Log File Descriptor                #
##################################################################

declare -r appErrorLog="/var/log/yourApp/error.log"

# Check to see if the error log is present.
if [[ ! -f "${appErrorLog}" ]]
then
    # Try to create the error log.
    if [[ ! touch "$appErrorLog" ]]
    then
        exit 1
    fi
fi

# Assign file descriptor number 2 to the application log: /var/log/<yourApp>/error.log
if [[ ! exec 2>> "${appErrorLog}" ]]
then
    exit 2
fi

##################################################################
#                        Load Libraries                          #
##################################################################

. ../library/Base/Base.sh
. ../classes/SignalHandler.sh
. ../classes/FileProcessingLogger
. ../classes/Validators/CommandValidator

################################################################################
################################################################################
################################################################################


################### APPLICATION HELPER FUNCTIONS #################


##################################################################
#            Functions that process each type of file.           #
##################################################################

# Note: The sed command returns zero, even if it does not change anything.

##
# Makes an edit to all file types.
# Changes blah, to foo bar.
#
# @param string $1 Name of the file to edit.
# @return bool
###
function generalEdit001 ()
{
    declare -r FILENAME=$1

    if sed -i -n -r 's/s/r/g' "$FILENAME"                     
    then                                                                         
        return 0        
    else                                                                   
        return 1                                        
    fi       
}

##
# The 1st edit for cash files.
# Changes blah, to foo bar.
#
# @param string $1 Name of the file to edit.
# @return bool
###
function cashEdit001 ()
{
    declare -r FILENAME=$1
    
    if sed -i -n -r 's/a/e/g' "$FILENAME"                     
    then                                                                         
        return 0        
    else                                                                   
        return 1                                        
    fi       
}

##
# The 2nd edit for cash files.
# Changes blah, to foo bar.
#
# @param string $1 Name of the file to edit.
# @return bool
###
function cashEdit002 ()
{
    declare -r FILENAME=$1

    if sed -i -n -r 's/i/o/g' "$FILENAME"                     
    then                                                                         
        return 0        
    else                                                                   
        return 1                                        
    fi       
}

##
# The 3rd edit for cash files.
# Changes blah, to foo bar.
#
# @param string $1 Name of the file to edit.
# @return bool
###
function cashEdit003 ()
{
    declare -r FILENAME=$1
    
    if sed -i -n -r 's/u/y/g' "$FILENAME"                     
    then                                                                         
        return 0        
    else                                                                   
        return 1                                        
    fi       
}

function processCash ()
{
    declare -ir GOOD_TRANSACTION=4
    declare -r FILENAME=$1

    declare -i numEdits=0

    # Attempt to prevent signals from interrupting the required edits.
    trap '' INT QUIT HUP ILL ABRT EMT BUS FPE SEGV PIPE TERM

    if generalEdit001 "$FILENAME"
    then                                            
        (( numEdits++ ))
    else
        return 1                  
    fi

    if cashEdit001 "$FILENAME"
    then                                                  
        (( numEdits++ ))
    else
        return 2
    fi

    if dogEdit002 "$FILENAME"
    then                                                   
        (( numEdits++ ))
    else
        return 3
    fi

    if dogEdit003 "$FILENAME"
    then                                        
        (( numEdits++ ))
    else
        return 4
    fi

    # Restore normal signal operations.
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
        logToApp "warning" "$BAD_MESSAGE"
        # Send alert or message to admin.
        return 0
    else
        logToApp "info" "$GOOD_MESSAGE"
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
        logToApp "notice" "$GOOD_MESSAGE"
    else
        logToApp "alert" "$BAD_MESSAGE"
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
    declare -r ERROR_DIR=$(echo $FILENAME | sed -n s//errors/)
    declare -r GOOD_MESSAGE="Notice: Moved file ${filename} to its error directory! PID=${PID} PPID=${PPID}"
    declare -r BAD_MESSAGE="Alert: Unable to move ${filename} to its error directory! PID=${PID} PPID=${PPID}"


    if mv -f $FILENAME $ERROR_DIR
    then
        logToApp "notice" "$GOOD_MESSAGE"
    else
        logToApp "alert" "$BAD_MESSAGE"
        # Send alert or message admin.
    fi
}

##
# Process all of the files in a directory iteratively, all while limiting processing time.
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
                moveBadFile $CURRENT_PID $lastJobPid $absoluteFilePath $ERROR_DIR
            fi
        fi
    done
}

##
# Processes all target directories.
#
# @author Anthony E. Rutledge
# @version 1.0
# @copyright (c) 2020, Anthony E. Rutledge
#
# @param string $1 The directory location of where to start from.
# @param string $2 The field in a listing (ls) to sort directories: mtime, size, or name.
# @param string $3 The order to process directories: asc or desc.
# @param string $4 The field in a listing (ls) to sort files: mtime, size, or name.
# @param string $5 The order to process files: asc or desc.
# @param int    $6 The maximim number of files to process, consecutively, in one pass.
# @param int    $7 The maximim time, in seconds, allowed per file before killing the process.
# @param int    $8 The maximum process checks allowed before the process gets killed.
# @param int    $9 The maximum amound of time, in seconds, between process checks.
#
# @return bool Returns 0 if all files were processed. Otherwize, non-zero is returned.
###
function processDirectories ()
{
    # --- Input Related Constants --- #

    # Where file types live in sub-directories. 
    # Where the program spends most of its time.
    declare -r SORTED_FILES_DIR="input/sorted/files/"
    
    # Where the procssing order for each sub-directory is recoreded and tracked in text files.
    declare -r SORTED_MANIFESTS_DIR="input/sorted/manifests/"
    #-------------------------
  
    # --- Output Related Constants --- #
    
    # Where completed work is stored in sub-directories by type.
    declare -r FINISHED_FILES_DIR="output/finished/files/"
    
    # Where final manifests live.
    declare -r FINISHED_MANIFESTS_DIR="output/finished/manifests/"
    #-------------------------

    # --- Error Output Related Constants --- #
    
    # Where bad files get moved to (in sub-directories by type).
    declare -r ERROR_FILES_DIR="output/errors/files/"
    
    # Where bad files get moved to (in sub-directories by type).
    declare -r ERROR_MANIFESTS_DIR="output/errors/manifests/"
    #-------------------------

    # Used to format the stderr ouput of the /usr/bin/time command.
    declare -r TIME_FORMAT="CPUKernel:%S CPUUser:%U CPUTotal:%P ExecTime:%E ExecSecs:%e MaxMemKB:%M AveResMemKB:%t AveTotalMemBK:%K FilesIn:%I FilesOut:%O SignalsIn:%k"

    # main()'s process ID.
    declare -r CURRENT_PID=$$

    # User / Program Input. See doc block above.
    declare -r ROOT_INPUT_DIR=$1
    declare -r DIR_SORT_KEY=$2
    declare -r DIR_SORT_ORDER=$3
    declare -r FILE_SORT_KEY=$4
    declare -r FILE_SORT_ORDER=$5
    declare -ir MAX_FILES_PER_DIR=$6
    declare -ir MAX_PROCESSING_SECONDS=$7
    declare -ir MAX_PROCESS_CHECKS=$8
    declare -ir MAX_DELAY_SECONDS=$9

    # The absolute path to the sorted file directories.
    declare -r TARGET_ROOT_PATH="${ROOT_INPUT_DIR}${SORTED_FILES_DIR}"
    
    # The absolute path to the finished file directories.
    declare -r FINSIHED_ROOT_PATH="${ROOT_INPUT_DIR}${FINISHED_FILES_DIR}"
    
    # The absolute path to the error file directories.
    declare -r ERROR_ROOT_PATH="${ROOT_INPUT_DIR}${ERROR_FILES_DIR}"
    
    # The absolute path to a specific directory of files under the TARGET_ROOT_PATH
    declare targetDir
    
    # The absolute path to a specific directory of files under the FINSIHED_ROOT_PATH
    declare finishedDir
    
    # The absolute path to a specific directory of files under the ERROR_ROOT_PATH
    declare errorsDir

    # Types of files to process. One directory per file type.
    # Todo: Replace this basic logic for determining directory
    #       processing order with a dynamic solution based on:
    #       1) DIR_SORT_KEY: mtime, size, name
    #       2) DIR_SORT_ORDER: asc or desc
    declare -Ar TARGET_DIRS=($(ls -ld "${TARGET_ROOT_PATH}"*/))

    # The number of directories to process.
    declare -ir TARGET_DIRS_LENGTH=${#TARGET_DIRS[@]}

    # Directories where all the files did not process successfully.
    declare -A errorDirs=()

    # The number of successfully processed directories.
    declare -i processedDirs=0

    # Iterate through all directories. 
    for fileTypeDir in "${TARGET_DIRS[@]}"
    do
        targetDir="${TARGET_ROOT_PATH}${fileTypeDir}"      # The exact set of files to work on.
        finishedDir="${FINSIHED_ROOT_PATH}${fileTypeDir}"  # Where to put fininshed files.
        errorsDir="${ERROR_ROOT_PATH}${fileTypeDir}"       # Where to put files with problems.
        
        fileProcessingFunction="process${fileTypeDir}"     # The name of the function to process the targetDir

        # Add log entry header. GitHub does not recognize <<- for here docs!
        cat << EOF 1>>&2
        ==========
        JOB START: 
        $(getDateTime) "$targetDir" $(hostname) $(hostname -i | awk '{print $2}') # Date Directory hostname IP: (DRY, make function.)
        $(getProcessReport $CURRENT_PID)
        ----------
EOF
        # The /usr/bin/time command will add important resource usage information to the log body.
        
        # The processFiles() function will iterate through the files of a directory,
        # all while applying "fileProcessingFunction" to those same files.
        
        if /usr/bin/time -f $TIME_FORMAT processFiles \
            "$fileProcessingFunction" $MAX_PROCESSING_SECONDS $MAX_PROCESS_CHECKS $MAX_DELAY_SECONDS "$targetDir" "$finishedDir" "$errorsDir"
        then
            # The file set was processed successfully.
            (( processedDirs++ ))
        else
            errorDirs+=$targetDir
            logToApp "err" "All $targetDir files were not processed!"
        fi

        # Add log entry footer.
        cat << EOF 1>>&2
        $(getDateTime) $targetDir $(hostname) $(hostname -i | awk '{print $2}')
        $(getProcessReport $CURRENT_PID)
        JOB END: 
        ==========
EOF
    done

    if (( processedDirs == TARGET_DIRS_LENGTH ))
    then
        logToApp "info" "Processing complete! All files in all directories were processed."
        return 0
    fi

    logToApp "warning" "File processing issues in at: ${errorDirs[*]}"
    return 3
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
# 6) Maintains per-directory transaction manifest: (in progress)
#
# 7) Cleans-up the environment before the process dies, unless SIGKILL (9) is issued.
#
# @param string $1 The directory location of where to start from.
# @param string $2 The field in a listing (ls) to sort directories: mtime, size, or name.
# @param string $3 The order to process directories: asc or desc.
# @param string $4 The field in a listing (ls) to sort files: mtime, size, or name.
# @param string $5 The order to process files: asc or desc.
# @param int    $6 The maximim number of files to process, consecutively, in one pass.
# @param int    $7 The maximim time, in seconds, allowed per file before killing the process.
# @param int    $8 The maximum process checks allowed before the process gets killed.
# @param int    $9 The maximum amound of time, in seconds, between process checks.
#
# @return bool Returns 0 if all files were processed. Otherwise, non-zero is returned.
###
function main ()
{
    ###################################################################
    ############## Variables for Command Line User Input ##############
    ########################### (Defaults) ############################

    # --- Where to Start! --- #

    # The uppermost parent directory for the entire taks to be done.
    declare rootInputDir="/var/local/yourApp/data/input/"
    # ----------------------------

    # --- Sorting Options --- #

    # The field in a listing (ls) upon which to sort directories.
    declare dirSortKey="mtime"

    # The order to process the customer directories under .../input/sorted/
    declare dirSortOrder="asc"

    # The field in a listing (ls) upon which to sort files.
    declare fileSortKey="mtime"

    # The order to process the customer files under ..../input/sorted/<customer>)
    declare fileSortOrder="asc"
    # ----------------------------

    # --- Processing Limits --- #

    # The maximum number of files to process in a customer folder at a time.
    declare maxFilesPerDir=100

    # The maximum number of seconds to attempt processing a file.
    declare maxFileProcessingSeconds=15

    # The maximum number of times allowed to check to see if process has finished.
    declare maxProcessChecks=10

    # The delay in seconds between process checks.
    declare maxDelaySeconds=1
    # ----------------------------

    ###################################################################
    ###################################################################
    ###################################################################

    ################################################################################
    #                         Command Line Option Lengend
    #
    # -r = The immediate parent directory (../) of all directories to process: "/var/local/<application>/data/input/"
    # -K = Directory sort key: mtime, size, or name
    # -O = Order of directory processing: asc (earlies/oldest/smallest to latest/newest/largest), or desc (large to small)
    # -k = File sort key: mtime, size, or name
    # -o = Order of file processing: file name (default), oldest, newest, smallest, largest
    # -q = maxFilesPerDir The max number of files to process in any one set: Default = 100
    # -s = maxFileProcessingSeconds for each file: Default = 15
    # -c = maxProcessChecks during the processing of a file: Default = 10
    # -w = maxDelaySeconds between process checks: Default = 1
    #
    ################################################################################
    # TODO: Add filter step before assigning the value of $OPTARG
    
    declare OPTIND

    while getopts :r:K:O:k:o:q:s:c:d: option
    do
        case "$option" in:
            r) rootInputDir=$OPTARG
            K) dirSortKey=$OPTARG
            O) dirSortOrder=$OPTARG
            k) fileSortKey=$OPTARG
            o) fileSortOrder=$OPTARG
            q) maxFilesPerDir=$OPTARG
            s) maxFileProcessingSeconds=$OPTORG
            c) maxProcessChecks=$OPTORG
            d) maxDelaySeconds=$OPTORG
            :) echo "Invalid argument to: $option"
           \?) echo "Invaild option supplied. Must be r, K, O, k, o, q, s, c, and/or d !!"
        esac
    done

    declare -Ar USER_INPUT=(
    [rootInputDir]=$rootInputDir
    [dirSortKey]=$dirSortKey
    [dirSortOrder]=$dirSortOrder
    [fileSortKey]=$fileSortKey
    [fileSortOrder]=$fileSortOrder
    [maxFilesPerDir]=$maxFilesPerDir
    [maxFileProcessingSeconds]=$maxFileProcessingSeconds
    [maxProcessChecks]=$maxProcessChecks
    [maxDelaySeconds]=$maxDelaySeconds)

    if validateCommandInput "${USER_INPUT[@]}"
    then
        # This could be made iterative, allowing one to reach
        # maxFilesPerDir, then come back to a directory after all of
        # the other kinds of files have been processed.
        # It depends on the scenario. You can also just run
        # super_file_processor iteratively, too.
        processDirectories "${USER_INPUT[@]}"
        exit 0
    fi
    
    logToSystem "notice" "super_file_processor was invoked with invalid values for the arguments!"
    exit 1
}

################################################################################

main "$@"
