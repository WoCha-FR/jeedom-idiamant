<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

class mqttiDiamant extends eqLogic {

  /* Dependencies */
  public static function dependancy_info() {
    $return = array();
    $return['log'] = log::getPathToLog(__CLASS__ . '_update');
    $return['progress_file'] = jeedom::getTmpFolder(__CLASS__) . '/dependance';
    $return['state'] = 'ok';

    if (file_exists(jeedom::getTmpFolder(__CLASS__) . '/dependence')) {
      $return['state'] = 'in_progress';
    } else {
      if (config::byKey('lastDependancyInstallTime', __CLASS__) == '') {
        $return['state'] = 'nok';
      } else if (!is_dir(realpath(dirname(__FILE__) . '/../../resources/mqtt4idiamant/node_modules'))) {
        $return['state'] = 'nok';
      } else if (!file_exists(__DIR__ . '/../../resources/mqtt4idiamant/idiamant-mqtt.js')) {
        $return['state'] = 'nok';
      } else if (config::byKey('mqttiDiamantRequire', __CLASS__) != config::byKey('mqttiDiamantVersion', __CLASS__)) {
        $return['state'] = 'nok';
      }
    }
    return $return;
  }

  public static function dependancy_end() {
    config::save('mqttiDiamantVersion', config::byKey('mqttiDiamantRequire', __CLASS__), __CLASS__);
  }
}

class mqttiDiamantCmd extends cmd {
}