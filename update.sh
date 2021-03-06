#!/bin/sh

if [ ! `which composer` ]; then
    echo "composer command not found."
    exit 1;
fi

if [ $# -eq 0 ]; then
    # include framework list
    . ./list.sh
    targets="$list"
else
    targets="${@%/}"
fi

for fw in $targets
do
    if [ -d "frameworks/$fw" ]; then
        echo "***** $fw *****"
        cd "frameworks/$fw"
        composer update
        cd ..
        cd ..
    fi
done