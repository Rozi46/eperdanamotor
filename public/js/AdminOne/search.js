$(document).ready(function () {
    const url = $('input[name="url_active"]').val() || window.location.pathname;

    function loadData() {
        const sort_name = $('input[name="sort_name"]').val() || 'created_at';
        const sort = $('input[name="sort"]').val() || 'DESC';
        const search = encodeURIComponent($('input[name="search"]').val() || '');
        const vd = $('select[name="vd"]').val() || '10';
        actfilter(url, sort_name, sort, 1, vd, search);
    }

    loadData();

    $('input[name="search"]').on('keyup change', function (e) {
        if (e.type === 'keyup' && e.keyCode !== 13) return;
        loadData();
    });

    $('select[name="vd"]').on('change', function () {
        loadData();
    });

    if ($('input[name="search"]').val()) {
        $('input[name="search"]').focus();
    }
});

function pageact(name, sort) {
    const url = $('input[name="url_active"]').val() || window.location.pathname;

    const getSearch = () => encodeURIComponent($('input[name="search"]').val());
    const getVd = () => $('select[name="vd"]').val();

    $('button[btn="page_awal"]').off().on('click', function () {
        actfilter(url, name, sort, 1, getVd(), getSearch());
    });

    $('button[btn="page_min"]').off().on('click', function () {
        const page = Math.max(parseInt($('input[name="current_page"]').val()) - 1, 1);
        actfilter(url, name, sort, page, getVd(), getSearch());
    });

    $('button[btn="page_plus"]').off().on('click', function () {
        const page = parseInt($('input[name="current_page"]').val()) + 1;
        actfilter(url, name, sort, page, getVd(), getSearch());
    });

    $('button[btn="page_akhir"]').off().on('click', function () {
        const page = parseInt($('input[name="last_page"]').val());
        actfilter(url, name, sort, page, getVd(), getSearch());
    });
}