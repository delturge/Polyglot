from Limits import Limits     # Enum
from Degrees import Degrees   # Enum
from LatLongRegex import LatLongRegex as Pattern # Enum
from Validator import Validator

# A class for validating coordinate pairs.

class CoordinateValidator(Validator):
    
    def __init__(self):
        super().__init__()
        
    def hasCoordKeys(self, coordDict):
        return ('lat' in coordDict) and ('long' in coordDict)
        
    def isCoordSet(self, angleStringList):
        if not self.isList(angleStringList):
            raise TypeError('Geographic coordinate pair is not in list form.')

        if not self.isEqual(len(angleStringList), Limits.MAX_COORD_LENGTH.value):
            raise ValueException('Geographic coordinates must come in sets of two (latitude, longitude)!')
            
        if not self.isPattern(Pattern.LATITUDE.value, angleStringList[0]) and self.isPattern(Pattern.LONGITUDE.value, angleStringList[1]):
            raise TypeError('Geographic coordinates are malformed because they are not floating point values!')

        return True
    
    def __isRealLatitude(self, lat):
        return self.isBetweenInc(lat, Degrees.MIN_LATITUDE.value, Degrees.MAX_LATITUDE.value)
        
    def __isRealLongitude(self, long):
        return self.isBetweenInc(long, Degrees.MIN_LONGITUDE.value, Degrees.MAX_LONGITUDE.value)
        
    def isRealCoordinate(self, lat, long):
        if not self.isFloat(lat):
            lat = float(lat)
            
        if not self.isFloat(long):
            long = float(long)
        
        return self.__isRealLatitude(lat) and self.__isRealLongitude(long)
    
    def areRealCoordinates(self, coordsDict):
        results = [];
        
        for coord in coordsDict:
            results.append(self.isRealCoordinate(coord['lat'], coord['long']))

        return results
