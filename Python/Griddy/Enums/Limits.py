from enum import Enum

# An enum holding the maximum values for 1) the number of elements in a coordinate collection, and
#                                        2) the number of elements in a grid collection

class Limits(Enum):
    MAX_COORD_LENGTH = 2  # Maximum elements per coordinate: (100.4, -122.3), or ['lat':100.4, 'long':-122.3]
    MAX_GRID_LENGTH = 4   # Maximum elements per Grid coordinate collection: (37.5, 122.9), (38.7, 88.9), (56.9, 99.4), (33.9, 77.5)
