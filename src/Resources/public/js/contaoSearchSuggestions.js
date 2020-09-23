(function contaoSearchSuggestions() {
    this.triggerSearchButton;
    this.triggerClearButton;
    var search = this;
    init();
    return this;

    function init() {
        window.addEventListener('load', function () {
            search.triggerSearchButton = document.getElementById('searchSuggestions');
            if (search.triggerSearchButton) {
                search.triggerSearchButton.addEventListener('click', function () {
                    triggerSearch();
                });
            }
            search.triggerClearButton = document.getElementById('clearSuggestions');
            if (search.triggerClearButton) {
                search.triggerClearButton.addEventListener('click', function () {
                    clearAllEntries();
                });
            }
        })
    }

    function triggerSearch() {
        var url = window.location.href;
        url = url.replace('&ref=', '&words=1&ref=');
        if (confirm(url)) {
           // location.replace(url);
            window.open(url);
        }

    }

    function clearAllEntries() {
        if (confirm('sie wollen wirklich alle wörter löschen?')) {
            window.open(window.location.href + '&clear_suggestions=1', '_top');
        }
    }


})()
