let autoMatchApp;

$('#btn-job-apply-automatch').on('click', function () {
    $('#autoMatchWizard').modal('show');
});

$('body').on('hidden.bs.modal','#autoMatchWizard', function () {
    // user should be redirected to tutor jobs page,
    // if all steps of wizard was completed
    if (autoMatchApp.step === autoMatchApp.finishedSuccessfullyStep) {
        toastr.success('Successfully applied');
        const redirectUrl = App.endpoints.tutoringJobs;
        window.location.href = redirectUrl;
    } else {
        autoMatchApp.reset();
    }
});

jQuery(document).on('ready', function () {
    Vue.use(
        VeeValidate,
        {
            delay: 0,
            locale: 'en',
        }
    );

    Vue.use(VueTheMask);

    autoMatchApp = new Vue({
        el: '#autoMatchWizard',
        data: {
            step: 1,
            stepFirst: 1,
            stepLast: 6,
            finishedSuccessfullyStep: 6,
            availability: null, // step 1
            rateSubmit: null, // step 2
            description: null, // step 3
            isOnlineBefore: null, // step 4
            btnIsLoading: false,
            isDesktop: false,
            stopInterval: false,
            intervalFunction: null,
        },
        created() {
            this.reset();
        },
        mounted() {
            this.$nextTick(function nextTickMounted() {
                this.startTimer();
            });
        },
        beforeDestroy() {
            // interval should be off before component destroy
            clearInterval(this.intervalFunction);
        },
        watch: {
            availability(val) {
                if (val === 1) {
                    this.nextStep();
                }
            },
            rateSubmit(val) {
                if (val === 1) {
                    this.nextStep();
                }
            },
            isOnlineBefore(val) {
                if (val !== null) {
                    this.nextStep();
                }
            },
        },
        methods: {
            reset() {
                this.step = this.stepFirst;
                this.availability = null; // step 1
                this.rateSubmit = null; // step 2
                this.description = null; // step 3
                this.isOnlineBefore = null; // step 4
                this.btnIsLoading = false;
            },
            isStepValid: function () {
                const name = 'step-' + this.step + '.*';

                return this.$validator.validate(name);
            },
            _nextStep: function () {
                if (this.step < this.stepLast) {
                    this.step++;
                }
            },
            nextStep: function () {
                this._nextStep();
            },
            submitApply: function (e) {
                e.preventDefault();
                const self = this;

                self.btnIsLoading = true;

                return jQuery.ajax({
                    type: 'POST',
                    url: `/apply-job/${autoMatchWizardData.jobId}/`,
                    data: {
                        description: this.description, // step 3
                        isOnlineBefore: this.isOnlineBefore, // step 4
                        },
                    })
                    .success(function () {
                        self._nextStep();
                    })
                    .error(function (data) {
                        self.btnIsLoading = false;

                        if (data.status === 500) {
                            toastr.error(data.responseText);
                        } else {
                            jQuery.each(data.responseJSON, function (name, value) {
                                toastr.error(value[0]);
                            });
                        }
                    });
            },
            stepActive: function (currentStep) {
                return {
                    active: this.step === currentStep,
                };
            },
            startTimer() {
                function formatSeconds(secs) {
                    function pad(n) {
                        return (n < 10 ? `0${n}` : n);
                    }

                    const h = Math.floor(secs / 3600);
                    const m = Math.floor(secs / 60) - (h * 60);
                    // eslint-disable-next-line no-mixed-operators
                    const s = Math.floor(secs - h * 3600 - m * 60);

                    return `${pad(h)}:${pad(m)}:${pad(s)}`;
                }

                let seconds = autoMatchWizardData.automatchTimerEnd;

                if ((seconds && seconds >= 0) && this.$refs.timer) {
                    // eslint-disable-next-line
                    this.intervalFunction = setInterval(function() {
                        // eslint-disable-next-line no-plusplus
                        seconds--;
                        this.$refs.timer.innerText = formatSeconds(seconds);
                        if (seconds <= 0) clearInterval(this.intervalFunction);
                        if (this.stopInterval) clearInterval(this.intervalFunction);
                    }.bind(this), 1000);
                } else {
                    this.$refs.timer.innerText = 'Automatch processing';
                }
            },
        },
    });
});
