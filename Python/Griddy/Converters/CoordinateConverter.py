from Converter import Converter

# A class for converting strings of geographic coordinates to a dictionaries.

class CoordinateConverter(Converter):
    
    TARGET = '),('
    REPLACEMENT = ')|('
    COORD_DELIMITER = '|'
    ANGLE_DELIMITER = ', '
    
    def __init__(self, coordinateValidator):
        super().__init__(coordinateValidator)
    
    # A method for converting a coordinate tuple into a dictionary.
    def __floatsToCoordDict(self, latFloat, longFloat):        
        return {'lat':latFloat, 'long':longFloat}    
    
    #A method that makes a nested dictionary of coordinates from a list of eight coordinate floats
    def __listOfAngleFloatsToDict(self, listOfAngleFloats):        
        gridDict = {'topLeft':None, 'topRight':None, 'botLeft':None, 'botRight':None}
        gridDict['topLeft'] = self.__floatsToCoordDict(listOfAngleFloats[0], listOfAngleFloats[1])
        gridDict['topRight'] = self.__floatsToCoordDict(listOfAngleFloats[2], listOfAngleFloats[3])
        gridDict['botLeft'] = self.__floatsToCoordDict(listOfAngleFloats[4], listOfAngleFloats[5])
        gridDict['botRight'] = self.__floatsToCoordDict(listOfAngleFloats[6], listOfAngleFloats[7])
        return gridDict

    # A method that returns an indexed list of eight (8) geographic angles (as strings)
    # from a list of four (4) coordinate pairs (also, strings).
    def __listOfCoordsToListOfAngles(self, listOfCoordStrings):
        listOfAngleStrings = []
        
        for coordString in listOfCoordStrings:
            angleStringList = coordString.split(self.ANGLE_DELIMITER, 1)
            
            if self.validator.isCoordSet(angleStringList):
                for i in range(len(angleStringList)):            
                    listOfAngleStrings.append(angleStringList[i].strip())

        return listOfAngleStrings
    
    # A method that makes an indexed list of four (4) geographic coordinates.
    def __gridStringToListOfCoords(self, gridString):
        listOfCoordStrings = []
        tmpList = gridString.strip().replace(self.TARGET, self.REPLACEMENT, 3).split(self.COORD_DELIMITER, 3)
        
        if self.validator.isEqual(len(tmpList), 4):
            for i in range(len(tmpList)):
                listOfCoordStrings.append(tmpList[i].strip())
        else:
            raise ValueError('The grid string does not have four recognized coordinates.')
        
        return listOfCoordStrings
    
    def gridStringToGridDict(self, gridString):
        listOfCoordStrings = self.stripParenthesis(self.__gridStringToListOfCoords(gridString))  # makes four elements from one string
        listOfAngleStrings = self.__listOfCoordsToListOfAngles(listOfCoordStrings)                 # makes eight elements from four strings
        listOfAngleFloats = self.listOfStringsToFloats(listOfAngleStrings)
        return self.__listOfAngleFloatsToDict(listOfAngleFloats)
