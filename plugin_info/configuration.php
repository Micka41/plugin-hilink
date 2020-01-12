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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}
?>


<form class="form-horizontal">
  <div class="form-group">
		<legend><i class="fa-list-alt"></i> {{Modem}}</legend>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Adresse du modem}}</label>
            <div class="col-lg-2">
                <input type="text" class="configKey form-control" data-l1key="huawei_adress" placeholder="192.168.2.1" />
            </div>
        </div>
		
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Login}}</label>
            <div class="col-lg-2">
                <input type="text" class="configKey form-control" data-l1key="huawei_login" placeholder="admin" />
            </div>
        </div>
		
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Mot de passe}}</label>
            <div class="col-lg-2">
                <input type="password" class="configKey form-control" data-l1key="huawei_password" />
            </div>
        </div>	
</form>

 <form class="form-horizontal">
	<legend><i class="fa-envelope"></i> {{Paramètrage SMS}}</legend>
	    <div class="form-group">
            <label class="col-lg-4 control-label">{{Pause dans la boucle du demon en secondes}}</label>
            <div class="col-lg-2">
                <input type="text" class="configKey form-control" data-l1key="DemonSleep" placeholder="20" />
            </div>
        </div>
		
		<div class="form-group">
            <label class="col-lg-4 control-label">{{Transfert des expéditeurs inconnus vers le téléphone}}</label>
            <div class="col-lg-2">
                <input type="text" class="configKey form-control" data-l1key="teltransfert" placeholder="+33600000000" />
            </div>
        </div>
		
	    <div class="form-group">
            <label class="col-lg-4 control-label">{{Nb de messages reçus sauvegardés}}</label>
            <div class="col-lg-2">
                <input type="text" class="configKey form-control" data-l1key="nbsmsinbox" placeholder="60"/>
            </div>
        </div>
		
	    <div class="form-group">
            <label class="col-lg-4 control-label">{{Nb de messages envoyés sauvegardés}}</label>
            <div class="col-lg-2">
                <input type="text" class="configKey form-control" data-l1key="nbsmsoutbox" placeholder="60"/>
            </div>
        </div>
    </div>

  </fieldset>
</form>


<script>
function hilink_postSaveConfiguration(){
  $.ajax({// fonction permettant de faire de l'ajax
  type: "POST", // methode de transmission des données au fichier php
  url: "plugins/hilink/core/ajax/hilink.ajax.php", // url du fichier php
  data: {
    action: "configuration",
  },
  dataType: 'json',
  error: function (request, status, error) {
    handleAjaxError(request, status, error);
  },
  success: function (data) { // si l'appel a bien fonctionné
  if (data.state != 'ok') {
    $('#div_alert').showAlert({message: data.result, level: 'danger'});
    return;
  }
}
});
}
</script>
</div>
</fieldset>
</form>
