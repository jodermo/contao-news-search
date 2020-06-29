(function contaoNewsSearch() {

    this.searchButtons = [];
    this.searchInputs = [];
    this.searchViews = [];
    this.searchButtonClass = 'extend-search';
    this.searchViewsClass = 'search-extension';
    this.searchInputClass = 'search-extended-input';

    this.isExtended = false;


    this.init = init;
    this.showSearchViews = showSearchViews;
    this.hideSearchViews = hideSearchViews;

    init();
    return this;

    function init() {
        window.addEventListener('load', () => {
            this.searchButtons = document.getElementsByClassName(this.searchButtonClass);
            this.searchViews = document.getElementsByClassName(this.searchViewsClass);
            this.searchInputs = document.getElementsByClassName(this.searchInputClass);
            initButtons();
        });

        window['article_categories'] = this;

    }

    function setInputValue(value) {
        for (const input of this.searchInputs) {
            input.value = value;
        }
    }

    function initButtons() {
        for (const button of this.searchButtons) {
            button.style.cursor = 'pointer';
            if (button.classList.contains('active')) {
                this.isExtended = true;
            }
            if (this.isExtended) {
                button.classList.add('active')
            }
            button.addEventListener('click', (e) => {
                e.preventDefault();
                toggleSearchViews();
            });
        }
    }

    function setButtonsActive() {
        for (const button of this.searchButtons) {
            button.classList.add('active');
        }
    }

    function setButtonsInactive() {
        for (const button of this.searchButtons) {
            button.classList.remove('active');
        }
    }

    function setButtonsInnerHtml(html) {
        for (const button of this.searchButtons) {
            button.innerHtml = '<span>as' + html + '</span>';
        }
    }


    function showSearchViews() {
        console.log('showSearchViews', this.searchViews);
        for (const view of this.searchViews) {

            view.classList.remove('closed');
            view.classList.add('open');
        }
        setButtonsActive();
        setInputValue(1);
        this.isExtended = true;
    }

    function hideSearchViews() {
        for (const view of this.searchViews) {
            view.classList.remove('open');
            view.classList.add('closed');
        }
        setButtonsInactive();
        setInputValue(0);
        this.isExtended = false;
    }

    function toggleSearchViews() {
        if (!this.isExtended) {
            showSearchViews();
        } else {
            hideSearchViews();
        }
    }
})()
