jQuery(function() {
    $(document).on('click', '.review-enable', function () {
        var id = $(this).data('id');
        var url = '/backend/enable-review/'+ id + '/';
        setReviewStatus(url);
    });

    $(document).on('click', '.review-disable', function () {
        var id = $(this).data('id');
        var url = '/backend/banned-review/'+ id + '/';
        setReviewStatus(url);
    });
});
function setReviewStatus(url)
{
    $.ajax({
        url: url,
        success: function (ans) {
            if (ans.error) {
                toastr.error('status change error');
            } else {
                toastr.success('status change');
                $('.student-info[data-id="'+ans.id+'"]').find('.review-status').text(ans.text);
            }
        }

    });
}
