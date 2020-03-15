#!/bin/bash

# Convert PDF to Images
rm ppm/*
pdftoppm -r 300 book.pdf ppm/page

# Conver Images to HTML
cd ppm
rm ../hocr/*
for f in *.ppm ; do tesseract $f ../hocr/$f hocr; done
