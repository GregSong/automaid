#!/bin/sh
DIR=`dirname $0`

if [ $# != 1 ]; then
  echo "Warning wrong input num. It should be:"
  echo "\tadd_imports.sh YAML_FILE_NAME"
  exit 1 ;
fi
input=$1;
imports=`egrep '^imports:' $input`

if [ -n "$imports" ] 
then
  # check if there is a line of am_services.yml
  gsed -i -f "$DIR/update_imports.sed" $input
else
  # add imports to the end of file
  gsed -i -f "$DIR/add_imports.sed" $input
fi
