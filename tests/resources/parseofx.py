#!/usr/bin/env python3

import sys
from ofxparse import OfxParser

ofx = OfxParser.parse(sys.stdin)
