#!/usr/bin/python3
import os
import re
import sys

exclude_dirs  = ["vendor", ".git", "storage"]
include_files = ["js", "txt", "php", "css"]

def clean_whitespace(filename):
    print(">", filename)
    contents = ""
    with open(filename) as file:
        for line in file.readlines():
            contents += line.rstrip()+"\n"
    contents = contents.rstrip()+"\n"

    with open(filename, "w") as file:
        file.write(contents)

if len(sys.argv) > 1:
    for filename in sys.argv[1:]:
        clean_whitespace(filename)
else:
    for root, dirs, files in os.walk("."):
        pat = "./(" + "|".join(exclude_dirs) + ")(/|$)"
        if re.match(pat, root):
            continue

        pat = ".*\.("+"|".join(include_files)+")$"
        for filename in files:
            if not re.match(pat, filename):
                continue
            filename = root+"/"+filename
            clean_whitespace(filename)
