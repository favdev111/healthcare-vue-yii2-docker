let bookApp;
let clock;
let isClockWasInited;

$('#show-book-modal-btn').on('click', function () {
    $('#bookTutorWizard').modal('show');
});

// reset form after close modal
$('body').on('hidden.bs.modal', function () {
    bookApp.reset();
});


// reset form after close modal
$('body').on('shown.bs.modal','#bookTutorWizard', function () {
    if (!isClockWasInited) {
        clock = $('#clock1').FlipClock(1199, {
            clockFace: 'MinuteCounter',
            countdown: true,
            autoStart: false,
        });

        isClockWasInited = true;
    }
});

jQuery(document).on('ready', function () {
    const listenerKeyup = function(e) {
        if ((e.keyCode === 13) && bookApp.isDesktop) {
            bookApp.nextStep();
        }
    };

    Vue.use(
        VeeValidate,
        {
            delay: 0,
            locale: 'en',
        }
    );

    const HOURLY_RATE = 59;

    function resetForm() {
        return {
            firstName: '',
            lastName: '',
            email: '',
            phoneNumber: '',
            startDate: '',
            timePreferences: null,
            schoolGradeLevelId: null,
            subjects: null,
            note: '',
            tutorId: bookWizardData.form.tutorId,
            tutorBookingId: null,
            hourlyRate: HOURLY_RATE,
            paymentAdd: [],
            zipCode: bookWizardData.form.zipCode,
            gclid: jQuery('#zc_gad_form #zc_gad').val(),
        };
    }

    Vue.use(VueTheMask);

    bookApp = new Vue({
        el: '#bookTutorWizard',
        data: {
            step: 1,
            stepFirst: 1,
            stepLast: 4,
            form: {},
            subjectElement: null,
            gradeElement: null,
            timePreferencesElement: null,
            btnIsLoading: false,
            subjectText: '',
            locationName: '',
            isDesktop: false,
            localZipCode: bookWizardData.form.zipCode,
            card: {},
            isClockWasStarted: false,
        },
        created() {
            this.reset();

            const isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0));
            if (!isTouch) {
                this.isDesktop = true;
            }
        },
        mounted() {
             jQuery('#timePreferencesBookWizard').on('initialized', () => {
                 this.timePreferencesElement = $('#timePreferencesBookWizard')[0].selectize;
                 this.timePreferencesElement.on('change', () => {
                     const timeValue = this.timePreferencesElement.getValue();
                     Vue.set(this.form, 'timePreferences', timeValue);
                 });

             });

             jQuery('#gradeBookTutorWizard').on('initialized', () => {
                 this.gradeElement = $('#gradeBookTutorWizard')[0].selectize;
                 this.gradeElement.on('change', () => {
                     const gradeValue = this.gradeElement.getValue();
                     Vue.set(this.form, 'schoolGradeLevelId', gradeValue);
                 });
             });

             jQuery('#subjectBookTutorWizard').on('initialized', () => {
                 this.subjectElement = $('#subjectBookTutorWizard')[0].selectize;
                 this.subjectElement.on('change', () => {
                     const subjectId = this.subjectElement.getValue();
                     Vue.set(this.form, 'subjects', subjectId);
                     this.subjectText = this.subjectElement.getItem(subjectId);
                 });
             });
         },
       watch: {
           ['form.subjects'](value) {
               if (this.subjectElement) {
                   this.subjectElement.setValue(value);
               }
           },
           ['form.schoolGradeLevelId'](value) {
               if (this.gradeElement) {
                   this.gradeElement.setValue(value);
               }
           },
           ['form.timePreferences'](value) {
               if (this.timePreferencesElement) {
                   this.timePreferencesElement.setValue(value);
               }
           },
           ['form.startDate'](val) {
               if (val === '') {
                   $('#datePickerBookTutorWizard').val('');
               }
           },
       },
        methods: {
            reset() {
                this.form = resetForm();

                if (typeof bookWizardData === 'object') {
                    for (let key in bookWizardData) {
                        if (!bookWizardData.hasOwnProperty(key)) {
                            continue;
                        }

                        let value = bookWizardData[key];
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
                this.card = {
                    address_zip: this.form.zipCode,
                };

                this.$validator.reset();
            },
            isStepValid: function () {
                const name = 'book-step-' + this.step + '.*';

                return this.$validator.validate(name);
            },
            _nextStep: function () {
                this.stepRequest();
            },
            nextStep: function () {
                const self = this;

                self.isStepValid().then(function(data) {
                    if ((self.step <= self.stepLast) && (data)) {
                        if (self.step === 3 && !self.form.startDate) {
                            toastr.error('The date field is required');
                            return;
                        }

                        self._nextStep();
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
            stepRequest() {
                let form = this.form;
                form.phoneNumber = this.form.phoneNumber.replace(/\D/gi, '');

                if (form.gclid === 'undefined') {
                    delete form.gclid;
                }

                this.btnIsLoading = true;
                const self = this;

                return jQuery.ajax({
                    type: 'POST',
                    url: `/book-tutor/?step=${this.step}/`,
                    data: form,
                    success: function (data) {
                        if (self.step === 1) {
                            self.form.tutorBookingId = data.tutorBookingId;
                        }

                        if (self.step === 3 ) {
                            if (!this.isClockWasStarted) {
                                clock.start();
                            }
                        }

                        self.btnIsLoading = false;

                        if (self.step === self.stepLast) {
                            self.submit();
                        } else {
                            self.step++;
                        }
                    },
                    error: function (data) {
                        self.btnIsLoading = false;

                        if (data.status === 500) {
                            toastr.error(data.responseText);
                        } else {
                            $.each(data.responseJSON, function (name, value) {
                                toastr.error(value[0]);
                            });
                        }
                    },
                });
            },
            submit() {
                this.btnIsLoading = true;
                let url = App.endpoints.bookTutorPayment + '?tutorBookingId=' + this.form.tutorBookingId;
                window.location.href = url;
                this.btnIsLoading = false;
            },
        },
    });

    jQuery('#subjectBookTutorWizard, #timePreferencesBookWizard, #gradeBookTutorWizard').selectize({
        onInitialize: function () {
            jQuery(this.$input).trigger('initialized');
        },
        onFocus: function () {
            this.clear();
        },
    });

    const datepicker = jQuery('#datePickerBookTutorWizard').kvDatepicker(
        {
            "autoclose": true,
            "startDate": new Date(),
            "format":"mm\/dd\/yyyy",
        });

    datepicker.on('changeDate', function () {
        const date = $("#datePickerBookTutorWizard").data('datepicker').getFormattedDate('yyyy-mm-dd');
        bookApp.form.startDate = date;
    });

    jQuery('#bookTutorWizard')
        .on('shown.bs.modal', function (e) {
            window.addEventListener('keyup', listenerKeyup);
        })
        .on('hide.bs.modal', function (e) {
            // prevent datepicker select date trigger from remove listener
            // (because of hide event triggered for some reason)
            if (e.target.id === 'datePickerBookTutorWizard') {
                return;
            }

            window.removeEventListener('keyup', listenerKeyup);
        });
});
