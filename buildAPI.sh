#!/bin/bash

rm -Rf docs/source/API/API
/usr/bin/env php vendor/bin/sami.php update docs/sami_configuration.php -v
sh -c "cd docs && make clean && make html"
