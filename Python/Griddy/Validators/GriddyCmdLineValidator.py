from FileValidator import FileValidator

# A class for validating command line input for GeoGrid.
class GriddyCmdLineValidator(FileValidator):

    def __init__(self, cmdArgsList):
        super().__init__()
        self.__quarentine = ['passwd', 'group', 'sudoers', 'httpd.conf', 'named.conf', 'php.ini',
                             'my.cnf', 'users.dat', 'ntusers.dat', 'system.dat', '/etc/passwd',
                             '/etc/group/', '/etc/shadow']
        self.__cmdArgsList = cmdArgsList
        self.__prepCmdArgs()
        self.__filterCmdArgs()
        self.__hasFileNames()
        self.__isNotCriticalFile()

    def getInput(self):
        return self.__cmdArgsList

    # A method that does basic checks on command line input.
    def __prepCmdArgs(self):
        if self.isGreater(len(self.__cmdArgsList), 1):
            self.__cmdArgsList = self.__cmdArgsList[1:]
        else:
            raise ValueError('You must supply at least one address file.')

        return

    # A method for very basic filtering of command line inputs. #Needs a filter module unto itself.
    def __filterCmdArgs(self):
        for i in range(len(self.__cmdArgsList)):
            self.__cmdArgsList[i] = str(self.__cmdArgsList[i]).strip()
            #More could be done with more research and time.

        return

    # A method that checks to see if the supplied file names
    # are actually files we might want to use.
    def __hasFileNames(self):
        for filePath in self.__cmdArgsList:
            if not self.isRealFullFile(filePath):
                raise OSError('The file ' + filePath + ' is not real, regular, readable, or is empty!')

        return

    # A method that does some primitive security checks.
    def __isNotCriticalFile(self):

        for file in self.__cmdArgsList:
            if file in self.__quarentine:
                raise RuntimeException("SECURITY! We've got a bear in the bushes!")

        return
