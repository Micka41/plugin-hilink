#!/bin/bash

PROGRESS_FILE=/tmp/dependancy_Hilink_in_progress
touch $PROGRESS_FILE
echo 0 > $PROGRESS_FILE
echo "********************************************************"
echo "*			 Installation des dépendances			 *"
echo "********************************************************"
sudo apt-get update
echo 30 > $PROGRESS_FILE
phpVersion="$(command readlink -f '/usr/bin/php' | command cut -c 10-)";
echo 40 > $PROGRESS_FILE
if [[  $phpVersion == "php7.3" ]]
  then
    echo "Installation pour php7.3"
	sudo apt update
	sudo apt install php7.3-bcmath
elif [[  $phpVersion == "php7" ]]
  then
    echo "Installation pour php7"
	sudo apt update
	sudo apt install php7.0-bcmath
	
elif [[  $phpVersion == "php5" ]]
  then 
	echo "Installation pour php5"
	sudo apt update
	sudo apt install php-bcmath
  else
	echo "Not found"
    echo $phpVersion
fi
echo 70 > $PROGRESS_FILE
pip install boto3
echo 100 > $PROGRESS_FILE
echo "********************************************************"
echo "*			 Installation terminée					*"
echo "********************************************************"
rm $PROGRESS_FILE