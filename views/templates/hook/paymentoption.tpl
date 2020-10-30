<section id="niubiz-payment-option" style="padding: 20px 0">
    <form id="niubizform" action="{$linkReturn|escape:'html':'UTF-8'}" method="POST">
        <script src="{$var.urlScript|escape:'html':'UTF-8'}"
        data-sessiontoken="{$var.sessionToken|escape:'htmlall':'UTF-8'}"
        data-merchantid="{$var.merchantId|escape:'htmlall':'UTF-8'}"
        data-channel="web"
        data-buttonsize=""
        data-buttoncolor=""
        data-merchantlogo="http://{$logo|escape:'htmlall':'UTF-8'}"
        data-merchantname=""
        data-formbuttoncolor="#0A0A2A"
        data-showamount=""
        data-purchasenumber="{$var.numOrden|escape:'htmlall':'UTF-8'}"
        data-amount="{$var.monto|escape:'htmlall':'UTF-8'}"
        data-cardholdername="{$var.firstname|escape:'htmlall':'UTF-8'}"
        data-cardholderlastname="{$var.lastname|escape:'htmlall':'UTF-8'}"
        data-cardholderemail="{$var.email|escape:'htmlall':'UTF-8'}"
        data-usertoken="{$var.userTokenId|escape:'htmlall':'UTF-8'}"
        data-recurrence=""
        data-frequency=""
        data-recurrencetype=""
        data-recurrenceamount=""
        data-documenttype="0"
        data-documentid=""
        data-beneficiaryid="NIUBIZ"
        data-productid=""
        data-phone=""
        data-expirationminutes='5'
        data-timeouturl="{$linkReturn|escape:'html':'UTF-8'}"
        /></script>
    </form>
</section>
