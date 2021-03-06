#!/usr/bin/python

from Grid import Grid
import requests

##
# A class designed to extend then functionality of a Grid.
# It uses the Geocoder module to find the latidute and logitude of street addresses
# and locations. Ulimately, it reports on which addresses or locations are within
# the domain of a geographic area (inclusive).
#
# @link https://pypi.org/project/geocoder/
# @link https://en.wikipedia.org/wiki/Geocoding
#
# @author Anthony E. Rutledge
# @version 1.0
# @copyright (c) 2017, Anthony E. Rutledge
###
class GeoGrid(Grid):

    ##
    # The GeoGrid constructor
    #
    # @param Validator gridValidator A validator for Grid dictionaries.
    # @param Geocoder geocoder "A simple and consistend geocoding library / object."
    # @param Validator geocoderValidator A validator for geocoder output.
    # @param File textFileReader An object for reading text files.
    # @param Converter coordinateConverter An object for converting strings of geographic coordinates into Python dictionaries.
    # @param View geoConsoleView An object for viewing output from a GeoGrid.
    # @param Validator cmdLineValidator An object for validating command line input for GeoGrid.
    #
    # @return GeoGrid
    ###
    def __init__(self, gridValidator, geocoder, geocoderValidator, textFileReader, coordinateConverter, geoConsoleView, griddyCmdLineValidator):
        super().__init__(gridValidator)
        self.__geocoder = geocoder
        self.__geocoderValidator = geocoderValidator
        self.__file = textFileReader
        self.__converter = coordinateConverter
        self.__view = geoConsoleView
        self.__cmdLineValidator = griddyCmdLineValidator

        self.__addressList = []     # Where addresess from a file are stored.
        self.__geoResultsList = []  # Where objects returned from Geocoder are stored.

        self.__inputFiles = self.__cmdLineValidator.getInput()
        self.__main()

    ##
    # A method that sets the the coordinates for a GeoGrid.
    # Uses the first line of an input file as its data source.
    #
    # Example first line:
    # (44.0, -84.5),(44.0, -82.5),(41.0, -84.5),(41.0, -82.5)
    #
    # @return string gridString
    ###
    def __loadGridString(self):
        while True:
            gridString = self.__file.getLine().strip()

            if not self.validator.isEqual(gridString, ''):
                break

        return gridString

    ##
    # A method used as a lambda (anonymous function) to process an address file's lines.
    #
    # @param File file A text file.
    #
    # @return None
    ###
    def __getAddressLines(self, file):
        for line in file:
            self.__addressList.append(line.strip())

        return

    ##
    # A method that gets addresses from a file.
    #
    # @param Function anonymousFn Process an address file's lines.
    #
    # @return None
    ###
    def __loadAddresses(self, anonymousFn):
        self.__file.processLines(anonymousFn)
        return

    ##
    # A method that gets latitude and longitude from a list of addresses and/or locations.
    #
    # @param list addresses A list of addresses.
    #
    # @return None
    ###
    def __getCoordFromAddress(self, address):
        self.__geoResultsList.append(self.__geocoder.google(address))
        return

    ##
    # A method that gets latitude and longitude from a list of addresses.
    #
    # @return None
    ###
    def __getCoordsFromAddresses(self):
        with requests.Session() as session:  # Might not really work correctly.
            for address in self.__addressList:
                self.__getCoordFromAddress(address)
        return

    # A method that should get latitude and longitude from a list of addresses, but
    # the output from Geocoder is bad. :-(
    #
    #[<[OK] Google - Geocode [700 E Cross St, Ypsilanti, MI 48198, USA]>,
    #<[OK] Google - Geocode [1501 Grant Rd, West Chester, PA 19382, USA]> ...]
    #def __getCoordsFromAddresses(self):
    #    with requests.Session() as session:
    #        for address in self.__addressList:
    #            self.__geoResultsList.append(self.__geocoder.google(address, session=session))
    #
    #    return

    ##
    # A method that eliminates addresses from a list that fall outside of
    # a range of degrees (lat / long) that define the GeoGrid.
    #
    # @return None
    ###
    def __pruneAddressList(self):
        goodAddresses = []

        for i in range(len(self.__addressList)):
            result = self.__geoResultsList[i]

            if not self.__geocoderValidator.isGoodResult(result):
                continue

            latitude = result.latlng[0]
            longitude = result.latlng[1]

            if self.validator.isOutsideGrid(latitude, longitude, self._topLeft, self._botLeft, self._topRight):
                continue

            goodAddresses.append(self.__addressList[i])

        self.__addressList = goodAddresses
        return

    ##
    # Gathers program data from at formatted text file.
    #
    # @param string filename The name of the file to open.
    #
    # @return None
    ###
    def __getInputData(self, filename):
        self.__file.setName(filename)                          # Set the name of the file to open for reading.
        self.__file.openFile()                             # Open file

        gridString = self.__loadGridString()               # Load the first line/record from the file.
        gridDict = self.__converter.gridStringToGridDict(gridString) # See Grid::getGrid for example of a grid dictionary.

        self.setGrid(gridDict)                             # Set the boundaries for the grid.
        self.__loadAddresses(self.__getAddressLines)       # A function passed in to perform the service of getting the address lines.
        self.__file.close()                                # Close file, if it needs to be.
        return

    ##
    # Prcessses address data by converting it to geo-spatial coordinates (lat / long)
    #
    # @return Boolean True (if there are no addresses to process), False otherwise.
    ###
    def __processData(self):
        numAddresses = len(self.__addressList)  # Count the number of addresses to lookup.

        if self.validator.isEqual(numAddresses, 0):
            return True
        else:
            if self.validator.isEqual(numAddresses, 1):
                self.__getCoordFromAddress(self.__addressList[0]) # You only need to get one address.
            elif self.validator.isGreater(numAddresses, 1):
                self.__getCoordsFromAddresses()                   # You need to call GeoGrid::__getCoordFromAddress within a loop. Not ideal.
            else:
                raise ValueError('An impossible value for the number of addresses to look up was input to the program.')

        return False

    ##
    # Displays which addresses from the file are inside the Grid
    #
    # @return None
    ###
    def __showAddressesInGrid(self):
        self.__view.setGridDict(self.getGrid())
        self.__view.setAddressList(self.__addressList)

        if self.validator.isEqual(len(self.__addressList), 0):
            self.__view.showNoAddresses()
        else:
            self.__view.showAddresses()
        
        return

    ##
    # The main line of the program.
    # Gathers, Prcessses, and Displays data.
    # (not very MVCish, but could be re-engineered to separate concerns ...)
    #
    # @return None
    ###
    def __main(self):
        try:
            for filename in self.__inputFiles:
                done = False

                #----------------- Input -----------------

                self.__getInputData(filename)

                #----------------- Processing ------------

                done = self.__processData()
                self.__pruneAddressList()           # Get rid of addresses outside the grid.

                #-----------------Output -----------------

                if done:
                    self.__view.showNoAddressData()  # You are done for this file.
                    continue

                self.__showAddressesInGrid()
        finally:
            self.__file.close()   # Close file, if it needs to be. See File::close

        return
