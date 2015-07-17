$(document).ready(function () {

$("div .user_info").click(function() {
var userId = $(this).attr('data-user-id');
$dialog = new UserDetailDialog();
$dialog.open({"user_id" : userId}, function() {});
});

    $(function () {

        $('.toggle').click(function (event) {
            event.preventDefault();
            var target = $(this).attr('href');
            $(target).toggleClass('hidden show');
        });

    });

});