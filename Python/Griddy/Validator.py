import re

# A base class that centralizes some basic tests.

class Validator:

    def __init__(self):
        pass

    def isIdentifier(self, name):
        try:
            name
            return True
        except NameError:
            return False

    def isNone(self, x):
        return x is None

    def isSet(self, name):
        return self.isIdentifier(name) and (not self.isNone(name))

    def isIn(self, key, collection):
        return key in collection

    def inObject(self, someObject, attribute):
        return hasattr(someObject, attribute)

    def isBool(self, value):
        return isinstance(value, bool)

    def isInt(self, num):
        return isinstance(num, int)

    def isFloat(self, num):
        return isinstance(num, float)

    def isString(self, num):
        return isinstance(num, str)

    def isObject(self, obj):
        return isinstance(obj, object)

    def isTuple(self, collection):
        return isinstance(collection, tuple)

    def isList(self, collection):
        return isinstance(collection, list)

    def isDict(self, collection):
        return isinstance(collection, dict)

    def isTrue(self, x):
        return x == True

    def isFalse(self, x):
        return x == False

    def isLess(self, x1, x2):
        return x1 < x2

    def isEqual(self, x1, x2):
        return x1 == x2

    def isGreater(self, x1, x2):
        return x1 > x2

    def isLessOrEqual(self, x1, x2):
        return x1 <= x2

    def isGreaterOrEqual(self, x1, x2):
        return x1 >= x2

    def isBetweenExc(self, x, low, high):
        return low < x and x < high

    def isBetweenInc(self, x, low, high):
        return low <= x and x <= high

    def isPattern(self, pattern, string):
        return  self.isNone(re.compile(pattern).match(string))
