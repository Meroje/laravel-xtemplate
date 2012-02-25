#!/bin/bash
##
# x:template - PHP based template engine
# Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
# 
# Released under the GPL License.
##
##
# Testsuite to be executed in the shell
# Author: Tobias Pohlen
# Version: 0.2
# Package: xtemplate.test
##
echo "x:template - PHP based template engine"
echo "Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>"
echo ""
echo "Released under the GPL License."
echo "Visit: http://xtemplate.net/"
echo "================================================================="
echo "x:template v0.4 test suite"
 
# Define the testcase directory
TEST_CASES="Test/Cases/*"

# Counter for the result
DONE=0
FAILED=0
echo ""
echo "Please type in the path to your PHP installtion. If you leave the"
echo "field blank, the environment path is used."
read -p "Path (e.g. '/usr/local/bin/php'): " PHP_PATH

if [ -z "$PHP_PATH" ]; then
    echo "Using environment path."
    PHP_PATH="php"
fi
echo ""
echo "Your PHP version"
eval "$PHP_PATH" -v

echo "Tests are being performed..."
echo ""
echo "================================================================="
echo ""
# iterate over the cases
for f in $TEST_CASES
do
    # Check if this is a directory and not a .DS_STORE or thumbs.db file
    if [ -d "$f" ]; then
        # Jump to the case folder
        cd $f

        let DONE=DONE+1 

        # Output the name of the test
        read HEADLINE < description.txt 

        printf "%-4s: %-50s" $DONE "$HEADLINE"

        # Execute the test
        RESULT=$("$PHP_PATH" -f index.php)

        if [ "$RESULT" != "true" ]; then
            echo "failed"
            let FAILED=FAILED+1 
        else
            echo "completed"
        fi

        # Jump back
        cd ../../../
    fi
done

echo ""
echo "================================================================="
echo ""
echo "Tests done: $DONE"
echo "Tests failed: $FAILED"

