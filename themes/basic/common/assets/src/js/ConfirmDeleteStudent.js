
var studentToRemoveId;
var studentRemoveElement;
var studentRemoveTrigger;

$('[name="students"]').on('select2:selecting', function (e) {
    studentRemoveTrigger = $(this),
        studentRemoveElement = $(e.params.args.originalEvent.target);

    var id = studentRemoveElement.data('id');

    if (!id) {
        id = studentRemoveElement.parent().data('id');
    }
    if (id) {
        e.preventDefault();
        studentRemoveTrigger.select2('close');
        studentToRemoveId = id;
        $('#confirmDeleteStudent').modal('show');
    }
});

$('#confirmDelete').on('click', function() {
    var id = studentToRemoveId;
    $.ajax({
        method: 'POST',
        url: createUrlWithParams(App.endpoints.tutor.removeStudent, [id]),
        success: function (ans) {
            if (ans.errors) {
                toastr.error('Student could not be removed. Please contact us for more information');
                return;
            }
            studentRemoveTrigger.find('option[value="'+id+'"]').remove();
            studentRemoveElement.parents('li').remove();
            $('#tutors-added-students li[data-userid="'+id+'"]').remove();
            var listStudent = $('#added-student-block li');
            if (!listStudent.length) {
                $('#added-student-block').remove();
                $('#tutors-added-students span.caret').remove();
                $('#tutors-added-students a').addClass('empty-students-block');
            }
            toastr.success('Student has successfully been removed');
        },
        complete: function (ans) {
            $('#confirmDeleteStudent').modal('hide');
        }
    });
});
