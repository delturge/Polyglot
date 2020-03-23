
from File import File

# A class for handling text files.

class TextFile(File):
    
    def __init__(self, name, mode, fileValidator):
        super().__init__(name, mode, fileValidator)
