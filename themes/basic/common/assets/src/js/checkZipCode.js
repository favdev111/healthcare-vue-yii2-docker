function checkZipCode(address) {
    var url = 'https://maps.googleapis.com/maps/api/geocode/json';
    return $.ajax({
        type: "GET",
        url: url,
        data: {
            address:address,
            key:App.gmapsApiKey
        }
    });
}
