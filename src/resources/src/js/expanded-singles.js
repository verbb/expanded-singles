// ==========================================================================

// Expanded Singles for Craft CMS
// Author: Verbb - https://verbb.io/

// ==========================================================================


(function($) {
    var $siteMenuBtn = $('#page-container').find('.sitemenubtn:first');
    var storedSiteId = Craft.getLocalStorage('BaseElementIndex.siteId');

    if (!storedSiteId) {
        storedSiteId = Craft.primarySiteId;
    }

    var updateSingleUrls = function(siteId = null) {
        $('#main-content #sidebar nav a[data-cp-nav]').each(function(i, e) {
            var siteUrls = $(this).data('site-urls');
            var url = siteUrls[siteId];

            console.log('Expanded Singles: ' + siteId + ': ' + url);

            if (!url) {
                return;
            }

            // Update if overlay link already exists, create and append if not
            var $link = $(this).parent().find('a.cp-nav-link-mask');

            if ($link.length) {
                $link.attr('href', url);
            } else {
                $link = $('<a class="cp-nav-link-mask" href="' + url + '">' + $(this).text() + '</a>');
                $(this).parent().append($link);
            }
        });
    }

    Garnish.requestAnimationFrame($.proxy(function() {
        // If we have a site menu
        if ($siteMenuBtn.length) {
            // Set links to stored siteId
            updateSingleUrls(storedSiteId);

            // Add listener when selecting site
            this.siteMenu = $siteMenuBtn.menubtn().data('menubtn').menu;
            
            this.siteMenu.on('optionselect', function(e) {
                var $option = $(e.selectedOption);

                updateSingleUrls($option.data('site-id'));
            });
        } else {
            // Set links to default link
            updateSingleUrls(storedSiteId);
        }
    }, this));

})(jQuery);

