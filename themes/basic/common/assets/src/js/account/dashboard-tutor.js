function firstDayInMonth(yourDate) {
    return new Date(yourDate.getFullYear(), yourDate.getMonth(), 1);
}

$(function () {
    $('#dashboard-submit-lesson').on('click', function () {
        var studentId = $('[name="students"]').val();
        var subjectId = $('[name="subjects"]').val();
        var subjectName = $('[name="subjects"] option:selected').text().replace('Subject','');

        var lessonDate = $('[name="lessonDate"]').val();
        var lessonStartTime = $('[name="lessonStart"]').val();
        var lessonFinishTime = $('[name="lessonFinish"]').val();

        var lessonStart = lessonDate + ' ' + lessonStartTime;
        var lessonFinish = lessonDate + ' ' + lessonFinishTime;

        var dateLessonFinish = new Date(lessonFinish);
        var dateLessonStart = new Date(lessonStart);

        if (!studentId || !subjectId || !lessonStartTime || !lessonFinishTime || !lessonDate || !submitLessonParams.isJobSelectedIfRequired()) {
            toastr.error('Please complete the form ');
            return;
        }

        var time = dateLessonFinish - dateLessonStart;
        var hours = parseInt(time/1000/3600);
        var minutes = parseInt((time/1000)%3600/60);
        var data = getLessonData();
        var self = $(this);
        self.button('loading');
        $.ajax({
            url: App.endpoints.tutor.calcAmount,
            data: data,
            success: function(ans) {
                self.button('reset');

                if (ans.errors || ans.warnings) {
                    CommonHelper.showAllErrors(ans);
                    return;
                }

                var payerName = ans.payerName;
                dateLessonStart = moment(dateLessonStart).format('MM/DD/YYYY h:mm a');
                dateLessonFinish = moment(dateLessonFinish).format('MM/DD/YYYY h:mm a');
                $('#submitLesson .lesson-subject-modal').html(subjectName + ' Lesson with '+ ans.studentName);
                $('#submitLesson .lesson-time-modal').html(dateLessonStart + ' ― ' + dateLessonFinish + (hours? ' (' + hours + ' hours ' + minutes + ' minutes)' : '('+ minutes + ' minutes)'));
                $('#submitLesson .lesson-text-modal').html(
                    '<span>' + payerName + '</span>'
                    + (ans.isCompanyClient ? '' : ' credit card')
                    + ' will be charged and  <span> $'
                    + parseFloat(ans.result.amount).toFixed(2)
                    + ' </span> will be  <br>deposited into your account ending in <b>'
                    + ans.bankAccountNumber
                    + '</b> on '
                    + ans.processDate
                );
                $('#submitLesson').modal('show');
            },
            error: function ($xhr) {
                self.button('reset');
                var data = $xhr.responseJSON;
                CommonHelper.showAllErrors(data);
            }

        });
    });

    $('#confirm-lesson-payment').on('click', function () {
        var self = $(this);
        self.button('loading');

        var data = getLessonData();
        $.ajax({
            method: 'POST',
            url: App.endpoints.tutor.paymentCreate,
            data: data,
            success : function (ans) {
                if (ans.errors) {
                    self.button('reset');
                    toastr.error('Payment could not be processed. Please contact us for more information.');
                    return;
                }
                $('#confirm-lesson-payment').prop( "disabled", true );
                window.location.reload();
            }
        });
    });

    function getLessonData() {
        var studentId = $('[name="students"]').val();
        var subjectId = $('[name="subjects"]').val();

        var lessonDate = $('[name="lessonDate"]').val();
        var lessonStartTime = $('[name="lessonStart"]').val();
        var lessonFinishTime = $('[name="lessonFinish"]').val();

        var lessonStart = lessonDate + ' ' + lessonStartTime;
        var lessonFinish = lessonDate + ' ' + lessonFinishTime;

        var dateLessonFinish = new Date(lessonFinish);
        var dateLessonStart = new Date(lessonStart);

        return {
            fromDate: moment(dateLessonStart).format(App.global.dateTimeFormat),
            toDate: moment(dateLessonFinish).format(App.global.dateTimeFormat),
            studentId: studentId,
            subjectId: subjectId,
            jobId: submitLessonParams.jobId,
        };
    }

    $(document).on('click', '.add-student-menu', function (e) {
        var chatUserId = $(this).parents('.dialog-wrapper').data('chat-id');
        $.ajax({
            url: createUrlWithParams(App.endpoints.tutor.addStudent, [chatUserId]),
            success: function (data) {
                if (data.errors) {
                    toastr.error('Student has already been added');
                    return;
                }
                if (data.userIsUnverified === true) {
                    toastr.error('Please ask the student to add a payment method to their account.');
                    return;
                }
                window.location.reload();
            }
        });
    });

    $('[name="students"], [name="jobs"]').on("select2:unselecting select2:select", function (e) {
        $('[name="subjects"]').val(null).trigger('change');
    });
});

var submitLessonParams = {
    isClient: false,
    jobId: null,
    studentId: null,
    clear: function () {
        this.isClient = false;
        this.jobId = null;
        this.studentId = null;
        this.hideJobSelect();
    },
    newStudentChosen: function (user) {
        this.studentId = user.id;
        this.isClient = user.isClient;
        if (this.isClient) {
            this.showJobSelect();
        } else {
            this.hideJobSelect();
        }
    },
    showJobSelect: function() {
        this.clearJob();
        this.getJobsWrapperElement().removeClass('hidden');
    },
    hideJobSelect: function() {
        this.clearJob();
        this.getJobsWrapperElement().addClass('hidden');
    },
    getJobsWrapperElement: function() {
        return $('.jobs-select-wrapper');
    },
    getJobsElement: function() {
        return this.getJobsWrapperElement().find('[name=jobs]');
    },
    clearJob: function() {
        this.jobId = null;
        this.getJobsElement().val(null).trigger('change');
    },
    newJobSelected: function(job) {
        this.jobId = job.id;
    },
    isJobSelected: function() {
        return !!this.jobId;
    },
    isJobRequired: function () {
        return this.isClient;
    },
    isJobSelectedIfRequired: function() {
        return !this.isJobRequired() || this.isJobSelected();
    },
};
