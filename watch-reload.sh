#!/bin/bash
nodemon --ext js,css,php \
    --verbose \
    --ignore vendor \
    --ignore node_modules \
    --ignore storage \
    --exec 'zombieteer --url=http://doctrac.local --reload'
