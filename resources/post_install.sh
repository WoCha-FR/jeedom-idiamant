#!/bin/bash

set -x  # make sure each command is printed in the terminal
echo "Pre installation de l'installation/mise à jour des dépendances mqttiDiamant"

PROGRESS_FILE=/tmp/jeedom_install_in_progress_mqttiDiamant
echo 50 > ${PROGRESS_FILE}

BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd ${BASEDIR}
source ../core/config/mqttiDiamant.config.ini &> /dev/null
echo "Version requise : ${mqttiDiamantRequire}"

curl -L -s https://github.com/WoCha-FR/mqtt4idiamant/archive/refs/tags/${mqttiDiamantRequire}.tar.gz | tar zxf -
mv mqtt4idiamant-${mqttiDiamantRequire} mqtt4idiamant

echo 60 > ${PROGRESS_FILE}
cd $BASEDIR/mqtt4idiamant
npm ci

echo 90 > ${PROGRESS_FILE}
chown www-data:www-data -R ${BASEDIR}/mqtt4idiamant