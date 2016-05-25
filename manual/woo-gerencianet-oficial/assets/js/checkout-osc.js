//0.4.2

var getPaymentToken;
$gn.ready(function(checkout) {
    getPaymentToken = checkout.getPaymentToken;
});