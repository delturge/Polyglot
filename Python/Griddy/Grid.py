##
#  A base super class for the family of Grid classes.
#
# @author Anthony E. Rutledge
# @version 1.0
# @copyright (c) 2017, Anthony E. Rutledge
###
class Grid:

    ##
    # The root super consturctor for all Grid objects.
    #
    # @param Validator gridValidator An object validates a geographic grid dictionarie.
    # @param dictionary gridDict A dictionary of nested dictionaries that contain one co-ordinate pair each.
    #
    # @return Grid
    ###
    def __init__(self, gridValidator, gridDict=None):
        self.validator = gridValidator

        if self.validator.isNone(gridDict):
            self._topLeft = None
            self._topRight = None
            self._botLeft = None
            self._botRight = None
        else:
            self.setGrid(gridDict)

    ##
    # Sets four co-ordinate (latitude & longitude) points to define a geographic area.
    #
    # @param dictionary gridDict A dictionary of nested dictionaries that contain one co-ordinate pair each.
    #
    # @return None
    ###
    def setGrid(self, gridDict):
        if self.validator.isGrid(gridDict):
            self._topLeft = gridDict['topLeft']
            self._topRight = gridDict['topRight']
            self._botLeft = gridDict['botLeft']
            self._botRight = gridDict['botRight']

        return

    ##
    # Gets the co-ordinate points for a Grid as a dictionary of dictionaries.
    #
    # @return dictionary literal
    ###
    def getGrid(self):
        return {'topLeft':self._topLeft, 'topRight':self._topRight, 'botLeft':self._botLeft, 'botRight':self._botRight}
