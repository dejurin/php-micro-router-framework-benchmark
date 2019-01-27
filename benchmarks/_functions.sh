#!/bin/sh
benchmark ()
{
    fw="$1"
    url="$2"
    #ab_log="output/$fw.ab.log"
    wrk_log="output/$fw.wrk.log"
    output="output/$fw.output"

    # get rpm
    #echo "ab -c 10 -t 3 $url"
    #ab -c 10 -t 3 "$url" > "$ab_log"
    echo "wrk -t10 -c10 -d3s --latency $url"
    wrk -t10 -c10 -d3s --latency "$url" > "$wrk_log"

    #rps=`grep "Requests per second:" "$ab_log" | cut -f 7 -d " "`
    rps=`grep "Requests/sec:" "$wrk_log" | cut -c 14-100 | sed -e 's/  */ /g' -e 's/^ *\(.*\) *$/\1/'`
    transfer=`grep "Transfer/sec:" "$wrk_log" | cut -c 14-100 | sed -e 's/  */ /g' -e 's/^ *\(.*\) *$/\1/'`

    echo "Requests per second: $rps, Transfer/sec: $transfer"
    #sleep 90

    # get time
    count=10
    total=0
    for ((i=0; i < $count; i++)); do
        curl "$url" > "$output"
        t=`tail -1 "$output" | cut -f 2 -d ':'`
        total=`php ./benchmarks/sum_ms.php $t $total`
    done
    time=`php ./benchmarks/avg_ms.php $total $count`

    # get memory and file
    memory=`tail -1 "$output" | cut -f 1 -d ':'`
    file=`tail -1 "$output" | cut -f 3 -d ':'`

    echo "$fw: $rps: $memory: $time: $file: $type" >> "$results_file"

    echo "$fw" >> "$check_file"
    #grep "Document Length:" "$ab_log" >> "$check_file"
    #grep "Failed requests:" "$ab_log" >> "$check_file"
    grep "Document Length:" "$wrk_log" >> "$check_file"
    grep "Failed requests:" "$wrk_log" >> "$check_file"
    grep 'Hello world!' "$output" >> "$check_file"
    echo "---" >> "$check_file"

    # check errors
    touch "$error_file"
    error=''
    #x=`grep 'Failed requests:        0' "$ab_log"`
    x=`grep 'Failed requests:        0' "$wrk_log"`
    if [ "$x" = "" ]; then
        #tmp=`grep "Failed requests:" "$ab_log"`
        tmp=`grep "Failed requests:" "$wrk_log"`
        error="$error$tmp"
    fi
    x=`grep 'Hello world!' "$output"`
    if [ "$x" = "" ]; then
        tmp=`cat "$output"`
        error="$error$tmp"
    fi
    if [ "$error" != "" ]; then
        echo -e "$fw\n$error" >> "$error_file"
        echo "---" >> "$error_file"
    fi

    echo "$url" >> "$url_file"

    echo
}
