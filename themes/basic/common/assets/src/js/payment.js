const stripe = new Stripe(App.stripe.publicKey);
const elements = stripe.elements();

// Custom styling can be passed to options when creating an Element.
// (Note that this demo uses a wider set of styles than the guide below.)
const style = {
    base: {
        fontSmoothing: 'antialiased',
        fontFamily: '"Proxima Nova", Helvetica, sans-serif',
        '::placeholder': {
            color: '#999',
        },
        fontSize: '16px',
        color: '#3e3e3e',
    },
    invalid: {
        color: '#eb1c26',
    }
};

const cardNumber = elements.create('cardNumber', {style: style, placeholder: 'Card Number'});
const cardCvc = elements.create('cardCvc', {style: style, placeholder: 'CVC'});
const cardExpiry = elements.create('cardExpiry', {style: style, placeholder: 'MM / YY'});

cardNumber.mount('#card-number');
cardCvc.mount('#card-cvc');
cardExpiry.mount('#card-expiry');

const urlBookId = window.location.href
    .split('?tutorBookingId=')[1]
    .replace(/[\D]/g, '');

let form = {
    paymentAdd: [],
    isTermsSigned: 0,
    tutorBookingId: urlBookId,
    hourlyRate: $("input:radio[name=payment-rate]:checked").val(),
};

function submitForm() {
    bookForm.attr("disabled", true);
    const zipCodeVal = $('#book-zipcode').val();

    if ($('#payment-term').prop("checked") === false) {
        toastr.error('Please agree to the terms of use');
        bookForm.attr("disabled", false);
        return;
    }

    submitStripeData();
}

function submitStepData() {
    bookForm.attr("disabled", true);
    jQuery.ajax({
        type: 'POST',
        url: `/book-tutor/?step=5/`,
        data: form,
        success: function () {
            toastr.success('Your request was successfully sent');
            bookForm.attr("disabled", false);
            window.location.href = App.endpoints.bookTutorComplete + '?tutorBookingId=' + form.tutorBookingId;
        },
        error: function (data) {
            bookForm.attr("disabled", false);
            if (data.status === 500) {
                toastr.error(data.responseText);
            } else {
                $.each(data.responseJSON, function (name, value) {
                    toastr.error(value[0]);
                });
            }
        },
    });
}

function submitStripeData() {
    let name;
    const creditCardName = $('#book-card-name').val();

    if (creditCardName) {
        name = creditCardName.trim();
    }

    const stripeForm = {};

    if (name) {
        stripeForm.name = name;
    }

    stripe.createToken(cardNumber, stripeForm).then(function(res) {
        bookForm.attr("disabled", true);

        if (res.error) {
            toastr.error(res.error.message);
            bookForm.attr("disabled", false);
        } else {
            form.paymentAdd.push(res.token.id);
            form.isTermsSigned = 1;
            submitStepData();
        }
    });
}

const bookForm = $("#book-payment-form");

bookForm.submit(function(e) {
    e.preventDefault();
    submitForm();
});

bookForm.onkeypress = function(e) {
    let key = e.charCode || e.keyCode || 0;

    if (key === 13) {
        e.preventDefault();
        submitForm();
    }
};

const packages = bookWizardData.packages;

$('input[name=payment-rate][name=payment-rate]').change(function() {
    const selectedRate = this.value;
    const selectedPackage = packages.filter(obj => {
        return obj.hourlyRate == selectedRate;
    })[0];

    const defaultRate = packages[0].hourlyRate;
    const selectedPackageHours = selectedPackage.hours;
    const selectedDiscount = selectedPackage.discount;
    const selectedPackageHourlyRate = selectedPackage.hourlyRate;
    const amount = defaultRate * selectedPackageHours;
    const total = amount - selectedDiscount;

    $('#payment-amount').text(`$${amount}.00`);
    $('#payment-amount-in-note').text(`$${total}.00`);
    $('#payment-discount').text(`$${selectedDiscount}.00`);
    $('#payment-total').text(`$${total}.00`);

    form.hourlyRate = selectedPackageHourlyRate;
});


