$(document).ready(function () {

$("div .user_info").click(function() {
var userId = $(this).attr('data-user-id');
$dialog = new UserDetailDialog();
$dialog.open({"user_id" : userId}, function() {});
});

    if (window.history && window.history.pushState) {

        $(window).on('popstate', function() {

            var hashLocation = location.hash;
            var hashSplit = hashLocation.split("#!/");
            var hashName = hashSplit[1];

            if (hashName !== '') {
                var hash = window.location.hash;
                if (hash === '') {

                    window.location = "/gtevent/page/" +$("span[data-page-info]").attr('data-page-info');
                }
            }

            return false;
        });

       window.history.pushState('forward', null, './#forward');
    }

});