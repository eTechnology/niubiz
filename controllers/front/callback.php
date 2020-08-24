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
            $fp1 = fopen(dirname(__FILE__). '/../../logs/callback.txt', 'w');
            fwrite($fp1, $input);
            fclose($fp1);
        }

        if ($input === '') {
            $this->response(403, 'This url is only use to Niubiz from Culqi.');
        } else {
            $event_json = json_decode($input, true);

            if (isset($event_json['operationNumber']) && isset($event_json['cip']) && isset($event_json['status'])) {
                if ($this->getCipIsValid($event_json)) {
                    switch ($event_json['status']) {
                        case 'Paid':
                            $ps_os_payment = Configuration::get('PS_OS_WS_PAYMENT');
                            break;

                        case 'Expired':
                            $ps_os_payment = Configuration::get('PS_OS_CANCELED');
                            break;

                        default:
                            $ps_os_payment = Configuration::get('PS_OS_CANCELED');
                    }

                    $id_order = $this->getOrderIdByCIP($event_json['cip']);

                    $history = new OrderHistory();
                    $history->id_order = $id_order;
                    $history->changeIdOrderState((int)$ps_os_payment, (int)$id_order);

                    $this->response(200, 'Order updated successfully.', $event_json);
                } else {
                    $this->response(422, $this->trans("The payment sent doen't match with the payment generated in the system.", [], 'Modules.Niubiz.Callback'));
                }
            } else {
                $this->response(422, $this->trans("It doesn't contain the correct structure JSON.", [], 'Modules.Niubiz.Callback'));
            }
        }
    }

    /**
     * Validacion de CIP y AMOUNT en las tablas de Prestashop.
     *
     * @param $response
     * @return bool
     */
    private function getCipIsValid($response) {
        $queryPagoEfectivo = "SELECT * FROM "._DB_PREFIX_."niubiz_pagoefectivo WHERE cip = '".$response['cip']."'";
        $isValid = false;

        if ($rowPagoEfectivo = Db::getInstance()->getRow($queryPagoEfectivo)) {
            $queryOrder = "SELECT COUNT(*) FROM "._DB_PREFIX_."orders WHERE id_order = '".$rowPagoEfectivo['id_order']."' AND total_paid_tax_incl = '".$response['amount']."'";

            if ($rowOrder = Db::getInstance()->getValue($queryOrder)) {
                $isValid = (bool) $rowOrder;
            }
        }

        return $isValid;
    }

    private function getOrderIdByCIP($cip) {
        $rowPagoEfectivo = Db::getInstance()->getRow("SELECT * FROM "._DB_PREFIX_."niubiz_pagoefectivo WHERE cip = '".$cip."'");
        return (int)$rowPagoEfectivo['id_order'];
    }

    private function response($code, $message, $payload = null) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['message' => $message, 'payload' => $payload]);
        die;
    }
}
