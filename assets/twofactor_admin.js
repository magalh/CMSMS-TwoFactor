$(document).ready(function() {
    $('a.cta').each(function() {
        var icon = $(this).data('icon') || 'ui-icon-arrowreturnthick-1-w';
        var text = $(this).text();
        $(this).addClass('ui-state-highlight ui-corner-all')
        .html('<span class="ui-icon ' + icon + '"></span><span class="ui-button-text">' + text + '</span>')
        .hover(function() {
            $(this).addClass('ui-state-hover');
        }, function() {
            $(this).removeClass('ui-state-hover');
        });
    });
});