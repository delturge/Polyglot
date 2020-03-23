# A base class that centralizes conversion logic.

class Converter:
    
    def __init__(self, validator):
        self.validator = validator

    #A method that strips parenthesis from the beginning and ending of a list of strings.
    def stripParenthesis(self, listOfParenStings):
        parenFreeStrings = []
        
        for parenString in listOfParenStings:
            parenFreeStrings.append(parenString.strip('()'))
        
        return parenFreeStrings

    #A method that converts strings to floats.
    def stringToFloat(self, string):
        if self.validator.isString(string):
            return float(string)
        else:
            raise TypeError('The value ' + string + ' is not a string')
        
    #A method that coverts a list of strings into floats.
    def listOfStringsToFloats(self, listOfStrings):
        listOfFloats = []
        
        for string in listOfStrings:
            listOfFloats.append(self.stringToFloat(string))

        return listOfFloats
