var eventList;

(new (function EventList() {

    var _this = eventList = this;
    var _page;

    this.init = function(page) {
        _page = page;
        _this.initPager();
    }

    this.initPager = function() {
        $('.event_list .list .pagination a').click(function(e) {
            e.preventDefault();
            _this.search($(this).attr("data-page"));
        });

        $('.event_list .list table tr td button').click(function() {
            var event_id = $(this).attr('data-event-id');

            GTm.dialog({
                header: "Delete Event Window" ,
                body:"Delete Event?",
                btnOk: {
                    label:"OK",
                    callback: function() {
                        var data =
                            {
                                event_logger_id:event_id,
                                page:_page
                            };

                        GTm.post("/gtevent/delete", data, function(data) {
                            $("#events").html(data.pagerHtml);
                            _this.initPager();
                        });
                    }
                },
                btnCancel : {}
            });
        });

        $(".event_list .list table tr td:not(:has(button))").click(function() {
            var eventId = $(this).parent().attr("data-event-id");

            window.location = "/gtevent/show/event_id/" + eventId;

            return false;
        });
    }

    this.search = function(page) {
        page = (page === undefined ? _page : page);
        var data = {
            "page": page
        };

        GTm.post("/gtevent", data, function(data) {
            $('.event_list .list').html(data.pagerHtml);
            _page = data.page;
            _this.initPager();
        });

        window.history.pushState(null, null, '/gtevent/page/'+ page);

    }

})());