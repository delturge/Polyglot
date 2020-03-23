from enum import Enum

# Home brew regular expressions for latitude and longitude angles.

class LatLongRegex(Enum):
    LATITUDE  = 'r/^-??(?:(?:(?:[0-9]{1}?|[1-8]{1}?[0-9]{1}?)(:?\.{1}?[0-9]{1-13}?)??)|9{1}?0{1}?(?:\.{1}?0{1-13}?)??)$/'
    LONGITUDE = 'r/^(-??(?:(?:(?:[0-9]{1}?|[1-9]{1}?[0-9]{1}?|1{1}?[1-7]{1}?[1-9]{1}?)(:?\.{1}?[0-9]{1-13}?)??))|1{1}?8{1}?0{1}?(\.{1}?0{1-13}?)??)$/'
