#!/bin/bash


echo "Début de l'installation"

phpVersion="$(command readlink -f '/usr/bin/php' | command cut -c 10-)";

if [[  $phpVersion == "php7" ]]
  then
    echo "Installation pour php7"
	sudo apt-get update
	sudo apt-get install php7.0-bcmath
	
elif [[  $phpVersion == "php5" ]]
  then 
	echo "Installation pour php5"
	sudo apt-get update
	sudo apt-get install php-bcmath
  else
	echo "Not found"
    echo $phpVersion
	
fi

echo "Fin de l'installation"