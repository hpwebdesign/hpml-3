# Changelog

## 1.0.0.5

- Fix Journal3 quick checkout: `Uncaught SyntaxError: redeclaration of let lat` (inline script now wrapped in an IIFE).
- Fix Journal3 quick checkout: `Map: Expected mapDiv ... null` (map now waits for the Vue-rendered container; `initMap` guards against missing elements).
- Fix map location in checkout.
- Journal3 OCMOD (`Theme Support/Journal3/install.xml`) bumped to 1.0.1.
