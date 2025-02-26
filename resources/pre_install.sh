#!/bin/bash

set -x  # make sure each command is printed in the terminal
echo "Pre installation de l'installation/mise à jour des dépendances mqttiDiamant"

PROGRESS_FILE=/tmp/jeedom_install_in_progress_mqttiDiamant
echo 5 > ${PROGRESS_FILE}

BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

if [ -d "${BASEDIR}/mqtt4idiamant" ]; then
  if [ -f "${BASEDIR}/mqtt4idiamant/state.json" ]; then
    cp ${BASEDIR}/mqtt4idiamant/state.json ${BASEDIR}/state.json
    echo "Backup token"
  fi
  rm -R ${BASEDIR}/mqtt4idiamant
fi

echo 15 > ${PROGRESS_FILE}
echo "Pre install finished"