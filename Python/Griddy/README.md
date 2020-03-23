What is Griddy?
===============

Griddy is a geocoding program.

https://pypi.org/project/geocoder/

https://en.wikipedia.org/wiki/Geocoding

It takes real world locations and determines if they fall
inside a set of four latitude and longitude coordinates (a grid square).

Griddy gets its input from text files:
1. addresses1.txt
2. addresses2.txt

Class Families:

Grid
====

1. Grid.py
2. GeoGrid.py

Validators
==========

Branch 1

1. Validator.py
2. CoordinateValidator.py (uses Degrees.py, Limits.py)
3. GridValidator.py       (uses Limits.py)
3. GeocoderValidator.py

Branch 2

1. Validator.py
2. FileValidator.py
3. GriddyCmdLineValidator

Views
=====

1. View.py
2. GeoConsoleView.py

Converters
==========

1. Converter.py
2. CoordinateConverter.py
