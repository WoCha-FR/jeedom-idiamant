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

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('mqttiDidmant');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>





<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, nom_du_plugin) -->
<?php include_file('desktop', 'mqttiDiamant', 'js', 'mqttiDiamant');?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js');?>