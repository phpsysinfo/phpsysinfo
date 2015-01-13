#!/bin/bash
FILES=`find ../ \( \( -iwholename '*tool*' -o -iwholename '*lang*' \) -prune -o -iname '*.php' \) -a -type f`

for entry in ${FILES}; do
  php -l ${entry}
  if [ $? -ne 0 ]
  then
    exit;
  fi
done

for entry in ${FILES}; do
  echo "running phpcs --standard=PEAR on ${entry}"
  phpcs --standard=PEAR ${entry}
done
