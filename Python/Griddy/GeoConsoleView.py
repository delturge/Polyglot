from View import View

# A class for viewing output from a GeoGrid.

class GeoConsoleView(View):

    def __init__(self, gridDict=None, addressList=None):
        super().__init__()
        self.__gridDict = gridDict
        self.__addressList = addressList

    def setGridDict(self, gridDict):
        self.__gridDict = gridDict

    def setAddressList(self, addressList):
        self.__addressList = addressList

    #A method for generating a report header.
    def __showReportHeader(self):
        print("\t\t\t\t**********In The Grid**************\n")
        print('North Latitude: ' + str(self.__gridDict['topLeft']['lat']) + '\n'
              'South Latidude: ' + str(self.__gridDict['botLeft']['lat']) + '\n'
              'West Longitude: ' + str(self.__gridDict['topLeft']['long']) + ' \n'
              'East Longitude: ' + str(self.__gridDict['topRight']['long']) + '\n')

    # A method that produces output when there are no addresses to check.
    def showNoAddressData(self):
        self.__showReportHeader()
        print("NO ADDRESSES IN FILE TO CHECK!\n\n")
        return

    # A method that produces output for nul results.
    def showNoAddresses(self):
        self.__showReportHeader()
        print("NO ADDRESSES FALL WITHIN THE GRID!\n\n")
        return

    # A method that details which addresses are in the Grid.
    def showAddresses(self):
        self.__showReportHeader()

        for address in self.__addressList:
            print(address + '\n')

        print("\n\n")
        return
