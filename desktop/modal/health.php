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
  throw new Exception('401 Unauthorized');
}
$plugin = plugin::byId('mqttiDiamant');
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="table-responsive">
  <table class="table table-condensed table-bordered tablesorter" id="table_healthiDiamant">
    <thead>
      <tr>
        <th data-sorter="false" data-filter="false">{{Image}}</th>
        <th data-sortable="true" data-sorter="inputs">{{Module}}</th>
        <th data-sortable="true" data-sorter="inputs">{{Maison}}</th>
        <th data-sortable="true" data-sorter="inputs">{{Passerelle}}</th>
        <th data-sortable="true" data-sorter="inputs">{{ID}}</th>
        <th data-sortable="true" data-sorter="inputs">{{Dernière communication}}</th>
        <th data-sortable="true" data-sorter="inputs">{{Date création}}</th>
      </tr>
    </thead>
    <tbody>
      <?php
      foreach ($eqLogics as $eqLogic) {
        echo '<tr>';
        if ($eqLogic->getImage() !== false) {
          echo '<td><img height="65" width="55" src="' . $eqLogic->getImage() . '"/></td>';
        } else {
          echo '<td><img height="65" width="55" src="' . $plugin->getPathImgIcon() . '"/></td>';
        }
        echo '<td><a href="' . $eqLogic->getLinkToConfiguration() . '" style="text-decoration: none;">' . $eqLogic->getHumanName(true, true) . '</a></td>';
        echo '<td><span class="label label-success" style="font-size : 1em;">' . $eqLogic->getConfiguration('homename') . '</span></td>';
        echo '<td><span class="label label-warning" style="font-size : 1em;">' . $eqLogic->getConfiguration('gateway') . '</span></td>';
        echo '<td><span class="label label-primary" style="font-size : 1em;">' . $eqLogic->getLogicalId() . '</span></td>';
        echo '<td><span class="label label-default" style="font-size : 1em;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
        echo '<td><span class="label label-default" style="font-size : 1em;">' . $eqLogic->getConfiguration('createtime') . '</span></td>';
        echo '</tr>';
      }
      ?>
    </tbody>
  </table>
</div>
<script>
  jeedomUtils.initTableSorter();
</script>