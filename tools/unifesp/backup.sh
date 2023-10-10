  #!/bin/bash

reldir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
cd $reldir

rm -r ../data/*

php download_backup_lattes.php

NOW=$(date +"%Y-%m-%d")
FILE="backup.$NOW.zip"

zip -r ../backup/$FILE ../data