from Limits import Limits   # Enum. Not really, but ...
from CoordinateValidator import CoordinateValidator

# A class for validating the proposed cooridnates (4) for a Grid.
# Also, it can deterimine if a cooridnate is outside of a Grid.

class GridValidator(CoordinateValidator):

    def __init__(self):
        super().__init__()

    # A method that checks the alignment of the points along
    # north and south parallels, and east and west meridians.
    def __checkCoordAlignment(self, gridDict):
        if not self.isEqual(gridDict['topLeft']['lat'], gridDict['topRight']['lat']):
            raise RuntimeError('The top angles of the target area are not on the same geographic parallel!')

        if not self.isEqual(gridDict['botLeft']['lat'], gridDict['botRight']['lat']):
            raise RuntimeError('The bottom angles of the target area are not on the same geographic parallel!')

        if not self.isEqual(gridDict['topLeft']['long'], gridDict['botLeft']['long']):
            raise RuntimeError('The left angles of the target area are not on the same geographic meridian!')

        if not self.isEqual(gridDict['topRight']['long'], gridDict['botRight']['long']):
            raise RuntimeError('The right angles of the target area are not on the same geographic meridian!')

    # A method that checks the format of the submitted coordinate pairs.
    def __checkCoordFormat(self, gridDict):
        for key, value in gridDict.items():
            if not self.isDict(value):
                raise TypeError('The ' + key + ' coordinate is not an instance of a dictionary!')

            if not self.isEqual(len(value), Limits.MAX_COORD_LENGTH.value):
                raise RuntimeError('The ' + key + ' coordinate must only have two angles.')

            if not self.isIn('lat', value):
                raise RuntimeError('The ' + key + ' angle for latitude is missing!');

            if not self.isIn('long', value):
                raise RuntimeError('The ' + key + ' angle for longitude is missing!');

    # A method that checks the format of the submitted grid itself.
    def __checkGridFormat(self, gridDict):
        if not self.isDict(gridDict):
            raise TypeError('Grid coordinates must come wrapped in a dictionary.')

        if not self.isEqual(len(gridDict), Limits.MAX_GRID_LENGTH.value):
            raise RuntimeError("Grid coordinates must come in groups of four dictionaries: {'topLeft':{lat:float, long:float}, ... }!")

        for key in gridDict:
            if not self.isIn(key, gridDict):
                raise RuntimeError('The ' + key + 'coordinate of the grid is missing!');

    # A method that determines of four cooridate pairs actually represents a Grid.
    def isGrid(self, gridDict):
        self.__checkGridFormat(gridDict)
        self.__checkCoordFormat(gridDict)
        self.__checkCoordAlignment(gridDict)
        return True

    def __isNorthOf(self, latitude, topLeft):
        return self.isGreater(latitude, topLeft['lat'])

    def __isSouthOf(self, latitude, botLeft):
        return self.isLess(latitude, botLeft['lat'])

    def __isEastOf(self, longitude, topRight):
        return self.isGreater(longitude, topRight['long'])

    def __isWestOf(self, longitude, topLeft):
        return self.isLess(longitude, topLeft['long'])

    def isOutsideGrid(self, latitude, longitude, topLeft, botLeft, topRight):
        return (self.__isNorthOf(latitude, topLeft) or
               self.__isSouthOf(latitude, botLeft) or
               self.__isEastOf(longitude, topRight) or
               self.__isWestOf(longitude, topLeft))
