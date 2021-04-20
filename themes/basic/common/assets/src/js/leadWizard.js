let app;

const phoneNumberUsCode = {
    getMessage: function (field) {
        return field + ' is not a valid phone number';
    },

    validate: function (value) {
        return new Promise(function (resolve) {
            const convertedValue = '+1' + value.replace(/\D/gi, '');
            const phoneNumber = libphonenumber.parsePhoneNumberFromString(convertedValue);
            const isValidCountry = phoneNumber && phoneNumber.country === 'US';

            resolve({ valid: isValidCountry });
        });
    }
};

function runLeadWizard(data = [], skipZeroStep = false) {
    app.reset();

    for (let key in app.form) {
        if (!app.form.hasOwnProperty(key)) {
            continue;
        }

        if (data.hasOwnProperty(key.toLowerCase())) {
            Vue.set(app.form, key, data[key.toLowerCase()]);
        } else  if (data.hasOwnProperty(key)){
            Vue.set(app.form, key, data[key]);
        }
    }

    if (skipZeroStep) {
        app.submit0().then(function () {
            jQuery('#landingWizard').modal('show');
        });
    } else {
        jQuery('#landingWizard').modal('show');
    }
}

jQuery(document).on('ready', function () {
    const listenerKeyup = function(e) {
        if ((e.keyCode === 13 || e.keyCode === 9) && app.isDesktop) {
            if (app.step === 5) {
                app.submit();
            } else {
                app.nextStep();
            }
        }
    };

    Vue.use(
        VeeValidate,
        {
            delay: 0,
            locale: 'en',
        }
    );

    function resetForm() {
        return {
            name: '',
            email: '',
            phone: '',
            description: '',
            subject: '',
            zipCode: '',
            distance: '15mi',
        };
    }

    Vue.use(VueTheMask);
    app = new Vue({
        el: '#wizardLeadForm',
        data: {
            step: 0,
            stepFirst: 0,
            stepLast: 6,
            form: {},
            subjectElement: null,
            maxRandom: 100,
            minRandom: 80,
            btnIsLoading: false,
            subjectText: '',
            locationName: '',
            isModal: 1,
            isSearchPage: 0,
            isDesktop: false,
        },
        computed: {
            showHeader: function () {
                return this.step > 1 && this.step < 6;
            },
        },
        created() {
            this.reset();

            const isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0));
            if (!isTouch) {
                this.isDesktop = true;
            }

            this.$validator.extend('phoneNumberUsCode', phoneNumberUsCode);
        },
        mounted() {
            jQuery('#subjectWizardLead').on('initialized', () => {
                this.subjectElement = $('#subjectWizardLead')[0].selectize;
                this.subjectElement.on('change', () => {
                    const subjectId = this.subjectElement.getValue();
                    Vue.set(this.form, 'subject', subjectId);
                    this.subjectText = this.subjectElement.getItem(subjectId);
                });

                this.btnIsLoading = false;
            });
        },
        watch: {
            ['form.subject'](value) {
                if (this.subjectElement) {
                    this.subjectText = this.subjectElement.setValue(value);
                }
            },
        },
        methods: {
            reset() {
                this.form = resetForm();

                if (typeof leadWizardData === 'object') {
                    for (let key in leadWizardData) {
                        if (!leadWizardData.hasOwnProperty(key)) {
                            continue;
                        }

                        let value = leadWizardData[key];
                        if (typeof value === 'object') {
                            for (let keySub in value) {
                                if (!value.hasOwnProperty(keySub)) {
                                    continue;
                                }

                                if (this[key].hasOwnProperty(keySub)) {
                                    Vue.set(this[key], keySub, value[keySub]);
                                }
                            }
                        } else {
                            if (this.hasOwnProperty(key)) {
                                Vue.set(this, key, value);
                            }
                        }
                    }
                }

                this.step = this.stepFirst;
                this.btnIsLoading = false;
            },
            randomizer: function () {
                return Math.floor(Math.random() * (this.maxRandom - this.minRandom + 1)) + this.minRandom;
            },
            isStepValid: function () {
                const name = 'step-' + this.step + '.*';

                return this.$validator.validate(name);
            },
            _nextStep: function () {
                if (this.step < this.stepLast) {
                    this.step++;

                    if (this.isDesktop) {
                        this.addFocusOnElement(this.step);
                    }
                }
            },
            nextStep: function () {
                const self = this;

                self.isStepValid().then(function(data) {
                    if ((self.step < self.stepLast) && (data)) {
                        self._nextStep();
                    }
                    else {
                        self.errors.items.forEach(function(error) {
                            toastr.error(error.msg);
                        });
                    }
                });
            },
            addFocusOnElement: function (step) {
                const self = this;

                setTimeout(function() {
                    switch (step) {
                        case 2:
                            self.$refs.step2.focus();
                            break;
                        case 3:
                            // for vue-the-mask use $el for focus
                            self.$refs.step3.$el.focus();
                            break;
                        case 4:
                            self.$refs.step4.focus();
                            break;
                        case 5:
                            self.$refs.step5.focus();
                            break;
                    }
                }, 100);
            },
            submit0: function () {
                const self = this;

                return self.isStepValid().then(function(data) {
                    if (data) {
                        self.btnIsLoading = true;

                        return jQuery.ajax({
                            type: 'POST',
                            url: `/check-zip-code/${self.form.zipCode}/`,
                        })
                            .success(function (data) {
                                self.form.zipCode = data.zipCode;
                                self.locationName = data.locationName;
                                self.btnIsLoading = false;
                                self._nextStep();
                            })
                            .error(function () {
                                toastr.error('Zip code is invalid. Please enter a valid zip code');
                                self.btnIsLoading = false;
                            });
                    } else {
                        self.errors.items.forEach(function(error) {
                            toastr.error(error.msg);
                        });
                    }
                });
            },
            submit: function () {
                const self = this;
                const updatedForm = Object.assign({
                    isModal: this.isModal,
                    isSearchPage: this.isSearchPage,
                    gclid: jQuery('#zc_gad_form #zc_gad').val(),
                }, this.form);

                self.isStepValid().then(function(data) {
                    if (self.step < self.stepLast && data) {
                        self.btnIsLoading = true;

                        jQuery.ajax({
                            type: 'POST',
                            url: App.endpoints.smallResultPopUpHandler,
                            data: updatedForm,
                            success: function() {
                                self.btnIsLoading = false;
                                self.nextStep();

                                if (App.googleConversions.lead) {
                                    App.analytics.trackConversion(
                                        App.googleConversions.lead.action,
                                        App.googleConversions.lead.label
                                    );
                                }
                            },
                            error: function(data) {
                                if (data.status === 500) {
                                    toastr.error(data.responseText);
                                } else {
                                    jQuery.each(data.responseJSON, function (name, value) {
                                        toastr.error(value[0]);
                                    });
                                }
                            }
                        });
                    }
                    else {
                        self.errors.items.forEach(function(error) {
                            toastr.error(error.msg);
                        });
                    }
                });
            },
            headerActive: function (currentStep) {
                return {
                    active: this.step >= currentStep,
                };
            },
            stepActive: function (currentStep) {
                return {
                    active: this.step === currentStep,
                };
            },
        },
    });

    jQuery('#subjectWizardLead').selectize({
        onInitialize: function () {
            jQuery(this.$input).trigger('initialized');
        },
        onFocus: function () {
            this.clear();
        },
    });

    if (leadWizardData.modalClass) {
        jQuery('#landingWizard').addClass(leadWizardData.modalClass);
    }

    jQuery('#landingWizard')
        .on('shown.bs.modal', function (e) {
            window.addEventListener('keyup', listenerKeyup);
        })
        .on('hide.bs.modal', function (e) {
            window.removeEventListener('keyup', listenerKeyup);
        });

    jQuery(document).on('click', '[data-wizard]', function (e) {
        e.preventDefault();

        const data = jQuery(e.target).data();
        runLeadWizard(data);
    });
});
