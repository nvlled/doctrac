#!/bin/bash

filter=$1

watch "phpunit --filter='$filter'" app tests \
    --wait=0.1 --interval=0.1 --ignoreDotFiles
