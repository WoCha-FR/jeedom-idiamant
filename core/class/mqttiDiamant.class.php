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
  /* Handle MQTT */
  public static function handleMqttMessage($_message) {
    /* Message pour nous ? */
    if (isset($_message[config::byKey('mqtt::topic', __CLASS__, 'idiamant')])) {
      $message = $_message[config::byKey('mqtt::topic', __CLASS__, 'idiamant')];
    } else {
      log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Le message reçu n\'est pas un message mqttiDiamant', __FILE__));
      return;
    }
    // Parcours des messages
    foreach( $message as $_key => &$_values) {
      // Key connected ignored
      if ( $_key == 'connected' ) {
        continue;
      }
      if (!isset($_values['name']) || $_values['name'] == ''){
        $_values['name'] = $_values[$_key];
      }
      log::add(__CLASS__, 'debug', json_encode($_values));
    }
    // Nouvel appareil ?
    $eqLogic = self::byLogicalId($_key, __CLASS__);
    // Création si besoin
    if (!is_object($eqLogic)) {
      $eqLogic = new mqttiDiamant();
      $eqLogic->setIsVisible(1);
      $eqLogic->setIsEnable(1);
      $eqLogic->setName($_values['name']);
    }
    // Paramétrages
    $eqLogic->setEqType_name(__CLASS__);
    $eqLogic->setLogicalId($_key);
    $eqLogic->setConfiguration('device', $_values['type']);
    $eqLogic->setStatus('warning', !$_values['reachable']);
    // Sauvegarde
    $eqLogic->save();
    // Traitement des données
    self::handleValues($_values);
  }

  private static function handleValues($values) {
    $eqLogicId = $values['id'];
    $eqLogic = self::byLogicalId($eqLogicId, __CLASS__);
    // Nettoyage des valeurs inutiles
    unset($values['id'], $values['name'], $values['type']);
    // Parcours des valeurs
    foreach ($values as $key => $value) {
      log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Mise à jour de ', __FILE__) . $key . __(' dans le module ', __FILE__) . $eqLogicId);
      $eqLogic->checkAndUpdateCmd($key, $value);
    }
  }

  /* Configuration Equipement depuis config file*/
  public function postSave() {
    if ($this->getConfiguration('applyDevice') != $this->getConfiguration('device')) {
      $this->setConfiguration('applyDevice', $this->getConfiguration('device'));
      $this->save();
      if ($this->getConfiguration('device') == '') {
        return true;
      }
      $device = self::devicesParameters($this->getConfiguration('device'));
      if (!is_array($device)) {
        return true;
      }
      $this->import($device,true);
      log::add(__CLASS__, 'info', '[' . __FUNCTION__ . '] ' . __('Création des commandes pour un module de type ', __FILE__) . $this->getConfiguration('device'));
    }
  }

  public static function devicesParameters($_device = '') {
    $return = array();
    $files = ls(__DIR__.'/../config/devices', '*.json', false, array('files', 'quiet'));
    foreach ($files as $file) {
      try {
        $return[str_replace('.json','',$file)] = is_json(file_get_contents(__DIR__.'/../config/devices/'. $file),false);
      } catch (Exception $e) {
        
      }
    }
    if (isset($_device) && $_device != '') {
      if (isset($return[$_device])) {
        return $return[$_device];
      }
      return array();
    }
    return $return;
  }

  /* Images */
  public function getImage() {
    if (file_exists(__DIR__.'/../config/devices/'.  $this->getConfiguration('device').'.png')){
      return 'plugins/mqttiDiamant/core/config/devices/'.  $this->getConfiguration('device').'.png';
    }
    return false;
  }

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

  /* Daemon */
  public static function deamon_info() {
    $return = array();
    $return['log'] = __CLASS__;
    $return['launchable'] = 'ok';
    $return['state'] = 'nok';
    if (self::isRunning()) {
      $return['state'] = 'ok';
    }
    if (!class_exists('mqtt2')) {
      $return['launchable'] = 'nok';
      $return['launchable_message'] = __('Le plugin MQTT Manager n\'est pas installé', __FILE__);
    } else {
      if (mqtt2::deamon_info()['state'] != 'ok') {
        $return['launchable'] = 'nok';
        $return['launchable_message'] = __('Le démon MQTT Manager n\'est pas démarré', __FILE__);
      }
    }
    // Dépendances
    if (self::dependancy_info()['state'] == 'nok') {
      $return['launchable'] = 'nok';
      $return['launchable_message'] = __('Dépendances non installées.', __FILE__);
    }
    return $return;
  }

  public static function deamon_start() {
    self::deamon_stop();
    $deamon_info = self::deamon_info();
    if ($deamon_info['launchable'] != 'ok') {
      throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
    }
    /* Nettoyage anciens Topic MQTT */
    mqtt2::removePluginTopicByPlugin(__CLASS__);
    /* On enregistre le topic actuel */
    mqtt2::addPluginTopic(__CLASS__, config::byKey('mqtt::topic', __CLASS__, 'idiamant'));
    $mqttInfos = mqtt2::getFormatedInfos();
    log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Informations reçues de MQTT Manager', __FILE__) . ' : ' . json_encode($mqttInfos));
    $mqtt_url = ($mqttInfos['port'] === 1883) ? 'mqtts://' : 'mqtt://';
    $mqtt_url .= (empty($mqttInfos['password'])) ? '' : $mqttInfos['user'].':'.$mqttInfos['password'].'@';
    $mqtt_url .= $mqttInfos['ip'].':'.$mqttInfos['port'];

    $appjs_debug = 'DEBUG_LEVEL=' . log::convertLogLevel(log::getLogLevel(__CLASS__));
    $appjs_path = realpath(dirname(__FILE__) . '/../../resources/mqtt4idiamant');
    chdir($appjs_path);

    $config = [
      'mqtt_url' => $mqtt_url,
      'mqtt_topic' => config::byKey('mqtt::topic', __CLASS__, 'idiamant'),
      'mqtt_verifcert'=> false,
      'clientId' => config::byKey('idiamant::cid', __CLASS__, 'vide'),
      'clientSecret' => config::byKey('idiamant::csecret', __CLASS__, 'vide')
    ];
    log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Ecriture fichier de configuration', __FILE__) . ' : ' . json_encode($config));
    file_put_contents('config.json', json_encode($config));

    // Lancement
    $cmd = $appjs_debug . ' /usr/bin/node ' . $appjs_path . '/idiamant-mqtt.js';
    log::add(__CLASS__, 'info', __('Démarrage du démon mqttiDiamant', __FILE__) . ' : ' . $cmd);
    exec(system::getCmdSudo() . $cmd . ' >> ' . log::getPathToLog('mqttiDiamantd') . ' 2>&1 &');

    $i = 0;
    while ($i < 30) {
      $deamon_info = self::deamon_info();
      if ($deamon_info['state'] == 'ok') {
        break;
      }
      sleep(1);
      $i++;
    }
    if ($i >= 30) {
      mqtt2::removePluginTopic(config::byKey('mqtt::topic', __CLASS__, 'idiamant'));
      log::add(__CLASS__, 'error', __('Impossible de démarrer le démon mqttiDiamant, consultez les logs', __FILE__), 'unableStartDeamon');
      return false;
    }
    message::removeAll(__CLASS__, 'unableStartDeamon');
    return true;
  }

  public static function deamon_stop() {
    log::add(__CLASS__, 'info', __('Arrêt du démon mqttiDiamant', __FILE__));
    $find = 'mqtt4idiamant/idiamant-mqtt.js';
    $cmd = "(ps ax || ps w) | grep -ie '" . $find . "' | grep -v grep | awk '{print $1}' | xargs " . system::getCmdSudo() . "kill -15 > /dev/null 2>&1";
    exec($cmd);
    $i = 0;
    while ($i < 5) {
      $deamon_info = self::deamon_info();
      if ($deamon_info['state'] == 'nok') {
        break;
      }
      sleep(1);
      $i++;
    }
    if ($i >= 5) {
      system::kill($find, true);
      $i = 0;
      while ($i < 5) {
        $deamon_info = self::deamon_info();
        if ($deamon_info['state'] == 'nok') {
          break;
        }
        sleep(1);
        $i++;
      }
    }
    mqtt2::removePluginTopic(config::byKey('mqtt::topic', __CLASS__, 'idiamant'));
    sleep(1);
  }

  public static function isRunning() {
    if (!empty(system::ps('mqtt4idiamant/idiamant-mqtt.js'))) {
      return true;
    }
    return false;
  }

}

class mqttiDiamantCmd extends cmd {
}