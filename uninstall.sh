#!/bin/sh

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
        echo "***** $fw remove setup files *****"
        cd "frameworks/$fw/_benchmark"
        . "unistall.sh"
        cd ..
        cd ..
    fi
done

cd "output"
echo "Remove log files"
rm -rf *.log
echo "Remove output files"
rm -rf *.output
cd ..