<?php
/**
 * 2007-2021 PrestaShop
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
 *  @copyright  2007-2016 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class NiubizCallbackModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $this->ajax = true;

        $input = file_get_contents("php://input");

        if (Configuration::get('NBZ_DEBUG')) {
            $date = new DateTime();
            $fp1 = fopen(dirname(__FILE__). '/../../logs/callback.txt', 'w');
            $fp2 = fopen(dirname(__FILE__). '/../../logs/'. $date->format('Ymd_His') . '_callback.txt', 'w');
            fwrite($fp1, $input);
            fwrite($fp2, $input);
            fclose($fp1);
            fclose($fp2);
        }
    }
}
