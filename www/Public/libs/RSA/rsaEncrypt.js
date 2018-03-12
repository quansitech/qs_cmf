(function(w){
    w.rsaEncrypt = function(data){
        var pem = "公钥";
        var key = RSA.getPublicKey(pem);
        var encrypted = RSA.encrypt(data, key);
        return encrypted;
    }
})(window);