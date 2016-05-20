//0.4.1

var getPaymentToken;
$gn.ready(function(checkout) {
    getPaymentToken = checkout.getPaymentToken;
});