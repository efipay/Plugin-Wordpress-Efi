//0.4.4

var getPaymentToken;
$gn.ready(function(checkout) {
    getPaymentToken = checkout.getPaymentToken;
});