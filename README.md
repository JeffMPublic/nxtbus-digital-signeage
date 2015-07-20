 Introduction
-----------------------------------------------------------------------------------------------
This is a simple sciprt that will reach out to the nextbus public api and return bus information 
for a given array of stopids. This script will display the next 5 buses for each stop. It will
display each stop for 30 seconds then fade to the next stop in the array. This is used for digital
signage and will be styled from the signage front end.

Requirements
-----------------------------------------------------------------------------------------------
Any webserver running php 5.0+

How to
-----------------------------------------------------------------------------------------------
You will need to get your route information from http://www.nextbus.com/
You will need to get your stopids from 
  http://webservices.nextbus.com/service/publicXMLFeed?command=routeConfig&r=wknd2&a=rutgers
  a = agency name IE (rutgers/mbta)
  r = route IE (a/10/SL4)
