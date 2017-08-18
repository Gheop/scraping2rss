#!/usr/bin/env python
#-*- coding: utf-8 -*-

import cfscrape
import sys


scraper = cfscrape.create_scraper()  # returns a CloudflareScraper instance
# Or: scraper = cfscrape.CloudflareScraper()  # CloudflareScraper inherits from requests.Session
print scraper.get(str(sys.argv[1])).content
#print scraper.get("http://torrent9.biz").content
