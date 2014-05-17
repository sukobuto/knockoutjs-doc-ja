window.twitterApi = (function () {
    var throttleFunction = function (fn, throttleMilliseconds) {
        var invocationTimeout;

        return function () {
            var args = arguments;
            if (invocationTimeout)
                clearTimeout(invocationTimeout);

            invocationTimeout = setTimeout(function () {
                invocationTimeout = null;
                fn.apply(window, args);
            }, throttleMilliseconds);
        };
    };

    var getTweetsForUsersThrottled = throttleFunction(function (userNames, callback) {
        if (userNames.length == 0)
            callback([]);
        else {
            var url = "https://api.twitter.com/1.1/search/tweets.json?q=";
            for (var i = 0; i < userNames.length; i++)
                url += "from:" + userNames[i] + (i < userNames.length - 1 ? " OR " : "");
            $.ajax({
                url: url,
                dataType: "jsonp",
                success: function (data) { callback($.grep(data.results || [], function (tweet) { return !tweet.to_user_id; })); }
            });
        }
    }, 50);

    return {
        getTweetsForUser: function (userName, callback) {
            return this.getTweetsForUsers([userName], callback);
        },
        getTweetsForUsers: function (userNames, callback) {
            return getTweetsForUsersThrottled(userNames, callback);
        }
    };
})();
