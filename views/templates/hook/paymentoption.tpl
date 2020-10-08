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
        data-cardholdername="{$customer->firstname|escape:'htmlall':'UTF-8'}"
        data-cardholderlastname="{$customer->lastname|escape:'htmlall':'UTF-8'}"
        data-cardholderemail="{$customer->email|escape:'htmlall':'UTF-8'}"
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

{literal}
<script>
    (function() {
        $('#payment-confirmation > .ps-shown-by-js > button').click(function(e) {
            var isNiubizSelected = $('.payment-options').find("input[data-module-name='niubiz']").is(':checked');

            if (isNiubizSelected) {
                e.preventDefault();
                document.querySelector('.start-js-btn.modal-opener.default').click();
                return false;
            }
        });
    })();
</script>
{/literal}
