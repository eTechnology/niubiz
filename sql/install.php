<?php
/**
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'visanetperu_session (
    id_token INT(11) NOT NULL AUTO_INCREMENT,
    id_cart INT(11),
    id_customer INT(11),
    sessiontoken VARCHAR(50),
    sessionkey VARCHAR(50),
    date_add timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_token)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'visanetperu_log (
    id_visa INT(11) NOT NULL AUTO_INCREMENT,
    id_order INT(11),
    id_cart INT(11),
    id_customer INT(11),
    aliasName VARCHAR(20),
    transactiontoken VARCHAR(50),
    fechayhora_tx VARCHAR(19),
    res_cvv2 VARCHAR(5),
    csimensaje VARCHAR(50),
    id_unico VARCHAR(15),
    usertokenuuid VARCHAR(50),
    eticket VARCHAR(50),
    decisioncs VARCHAR(50),
    csiporcentajedescuento VARCHAR(50),
    nrocuota VARCHAR(2),
    cardtokenuuid VARCHAR(50),
    csiimportecomercio VARCHAR(50),
    csicodigoprograma VARCHAR(50),
    dsc_eci VARCHAR(100),
    eci VARCHAR(2),
    dsc_cod_accion VARCHAR(100),
    nom_emisor VARCHAR(50),
    impcuotaaprox VARCHAR(9),
    csitipocobro VARCHAR(50),
    numreferencia VARCHAR(50),
    respuesta VARCHAR(1),
    numorden VARCHAR(9),
    codaccion VARCHAR(3),
    imp_autorizado VARCHAR(10),
    cod_autoriza VARCHAR(50),
    codtienda VARCHAR(9),
    pan VARCHAR(19),
    reviewtransaction VARCHAR(50),
    ori_tarjeta VARCHAR(3),
    date_add TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (id_visa)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
