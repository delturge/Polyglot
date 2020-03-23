from TextFile import TextFile

# A class for reading text files.

class TextFileReader(TextFile):
    
    def __init__(self, fileValidator, name=None):
        super().__init__(name, 'rt', fileValidator)

    def setName(self, name):
        if not self.validator.isString(name):
            raise TypeError('File names must be strings!')

        if not self.validator.exists(name):
            raise TypeError('The file ' + name + ' does not exist! Check spelling.')
        
        if self.validator.isEmpty(name):
            raise TypeError('The file ' + name + ' is empty!')
        
        if not self.validator.isRegular(name):
            raise TypeError('The file ' + name + ' is not a regular file! Text files only.')
        
        if not self.validator.isReadable(name):
            raise TypeError('The file ' + name + ' is not readable! Check permissions.')

        super().setName(name)
        return

    def getLine(self):
        return self._file.readline().rstrip('\n')

    def getLines(self):
        return [line.rstrip('\n') for line in self._file]
    
    def processLines(self, anonymousFn):
        with self._file as file:
            anonymousFn(file)
        return
