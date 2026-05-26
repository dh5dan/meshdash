<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;
echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';

echo '<head><title data-i18n="submenu.nina_info.lbl.title">NINA-Warnmeldungen</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';

echo '<script type="text/javascript" src="../jquery/jquery.ui.autocomplete.html.js"></script>';
echo '<link rel="stylesheet" href="../css/autocomplete.css?' . microtime() . '">';

if ((getParamData('darkMode') ?? 0) == 1)
{
    echo '<link rel="stylesheet" href="../css/dark_mode.css?' . microtime() . '">';
}
else
{
    echo '<link rel="stylesheet" href="../css/normal_mode.css?' . microtime() . '">';
}

echo '<link rel="stylesheet" href="../css/nina_info.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../include/func_js_nina_info.php';
require_once '../include/func_php_nina_info.php';
require_once '../include/func_php_nina_api.php';
require_once '../include/func_js_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;
$btnText  = '<span data-i18n="submenu.nina_info.lbl.load-page-new">Gewählte NINA Kategorie(n) Abrufen</span>';

$mowasTypeChecked = $_POST['ninaMowsTypeChkBox'] ?? [];
$ninaArsId        = trim($_POST['ninaArsId'] ?? '');
$warningId        = trim($_POST['warningId'] ?? '');
$ninaArsRegion    = trim($_POST['ninaArsRegion'] ?? '');

$dashboardChk = in_array('nina', $mowasTypeChecked) ? 'checked' : '';
$katwarnChk   = in_array('katwarn', $mowasTypeChecked) ? 'checked' : '';
$biwappChk    = in_array('biwapp', $mowasTypeChecked) ? 'checked' : '';
$mowasChk     = in_array('mowas', $mowasTypeChecked) ? 'checked' : '';
$dwdChk       = in_array('dwd', $mowasTypeChecked) ? 'checked' : '';
$lhpChk       = in_array('lhp', $mowasTypeChecked) ? 'checked' : '';
$policeChk    = in_array('police', $mowasTypeChecked) ? 'checked' : '';
$warningChk   = in_array('warning', $mowasTypeChecked) ? 'checked' : '';

if ($sendData == 0)
{
    $ninaArsRegion = $ninaArsRegion == '' ? getParamData('ninaArsRegion') : $ninaArsRegion;
    $ninaArsId     = $ninaArsId == '' ? getParamData('ninaArsId') : $ninaArsId;
}

echo '<h2><span data-i18n="submenu.info_info.lbl.title">NINA-Warnsystem</span></h2>';

echo '<form id="frmNinaInfo" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';

echo '<table class="table">';

echo '<tr>';
echo '<td colspan="3">
        ARS-Region:<input type="text" name="ninaArsRegion" id="ninaArsRegion" value="' . $ninaArsRegion . '" placeholder="Such-/Eingabefeld" />
      </td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="3">
        ARS-Regionalschlüssel:<input type="text" name="ninaArsId" id="ninaArsId" value="' . $ninaArsId . '" />&nbsp;<span class="ninaInfo">(nur für NINA)</span>
      </td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="3">
        Warning KAT-ID:<input type="text" name="warningId" id="warningId" size="40"  value="' . $warningId . '" />&nbsp;<span class="ninaInfo">(nur für Warnung)<span>
      </td>';
echo '</tr>';

echo '<tr>';

echo '<td colspan="3" class="nina-types">';

echo '
<label class="nina-option">
    <input type="checkbox" name="ninaMowsTypeChkBox[]" ' . $dashboardChk . ' value="nina" />
    <span class="nina-label" data-type="nina">Nina</span>
</label>
';

echo '
<label class="nina-option">
    <input type="checkbox" name="ninaMowsTypeChkBox[]" ' . $katwarnChk . ' value="katwarn" />
    <span class="nina-label" data-type="katwarn">Katwarn</span>
</label>
';

echo '
<label class="nina-option">
    <input type="checkbox" name="ninaMowsTypeChkBox[]" ' . $biwappChk . ' value="biwapp" />
    <span class="nina-label" data-type="biwapp">Biwapp</span>
</label>
';

echo '
<label class="nina-option">
    <input type="checkbox" name="ninaMowsTypeChkBox[]" ' . $mowasChk . ' value="mowas" />
    <span class="nina-label" data-type="mowas">Mowas</span>
</label>
';

echo '
<label class="nina-option">
    <input type="checkbox" name="ninaMowsTypeChkBox[]" ' . $dwdChk . ' value="dwd" />
    <span class="nina-label" data-type="dwd">Dwd</span>
</label>
';

echo '
<label class="nina-option">
    <input type="checkbox" name="ninaMowsTypeChkBox[]" ' . $lhpChk . ' value="lhp" />
    <span class="nina-label" data-type="lhp">Lhp</span>
</label>
';

echo '
<label class="nina-option">
    <input type="checkbox" name="ninaMowsTypeChkBox[]" ' . $policeChk . ' value="police" />
    <span class="nina-label" data-type="police">Polizei</span>
</label>
';

echo '
<label class="nina-option">
    <input type="checkbox" name="ninaMowsTypeChkBox[]" ' . $warningChk . ' value="warning" />
    <span class="nina-label" data-type="warning">Warnung</span>
</label>
';

echo '</td>';

echo '<tr>';

echo '<td colspan="3">
        <button type="button" class="btnLoadLoraNinaInfo" id="btnNinaMowasType">' . $btnText . '</button>
      </td>';

echo '</tr>';

if ($sendData == 1)
{
    setParamData('ninaArsRegion', $ninaArsRegion, 'text');
    setParamData('ninaArsId', $ninaArsId, 'text');

    foreach ($mowasTypeChecked AS $mowasType)
    {
        $params = [
            'mowasType' => $mowasType,
            'ars'       => $ninaArsId,
            'warningId' => $warningId,
        ];

        $data = getNinaData($params);
        showNinaInfo($data, $params);
    }
}

echo '<table>';
echo '</form>';
echo '<div id="pageLoading" class="pageLoadingSub"></div>';
echo '<script>
            $.getJSON("../translation.php?lang=' . $userLang . '", function(dict) {
            applyTranslation(dict); // siehe JS oben
            });
        </script>';
echo '</body>';
echo '</html>';
