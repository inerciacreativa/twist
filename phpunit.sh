#!/bin/bash
phpunit_bin() {
	if [[ -f ./vendor/bin/phpunit ]]; then
		./vendor/bin/phpunit --configuration=$1
	else
		WD=$PWD
		cd ./../../../..
		echo $PWD
		./vendor/bin/phpunit --configuration=$1 --bootstrap=./vendor/autoload.php
		cd ${WD}
	fi
}

phpunit_bin	${PWD}/phpunit.xml
