#!/bin/sh

setup_log="../../output/setup.log"

if [ ! `which composer` ]; then
    echo "composer command not found."
    exit 1;
fi

if [ ! `which wrk` ]; then
    echo "wrk command not found. Go to https://github.com/wg/wrk"
    exit 1;
fi

if [ ! `which curl` ]; then
    echo "curl command not found."
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
        . "_benchmark/setup.sh"
        echo "frameworks/$fw/_benchmark/setup.sh" >> "$setup_log"
        cd ..
        cd ..
    fi
done

find . -name ".htaccess" -exec rm -rf {} \;