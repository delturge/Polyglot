# A base class for handling files.

class File:

    def __init__(self, name, mode, fileValidator):
        self.__name = name
        self.__mode = mode
        self._file = None
        self.validator = fileValidator

    def setName(self, name):
        self.__name = name
        return

    def openFile(self):
        self._file = open(self.__name, self.__mode, 1, 'utf_8', 'strict', None)
        
        if not self.validator.isStringIO(self._file):
            raise TypeError('A proper file handle was not created!')
        
        return

    def close(self):
        if not self.validator.isNone(self._file):
            self._file.close()
            self._file = None
            self.__name = None

        return
