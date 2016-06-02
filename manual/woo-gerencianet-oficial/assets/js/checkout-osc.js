//0.4.3

var getPaymentToken;
$gn.ready(function(checkout) {
    getPaymentToken = checkout.getPaymentToken;
});