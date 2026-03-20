$(document).ready(function () {
    const url = $('input[name="url_active"]').val() || window.location.pathname;

    function formatDate(inputName) {
        const val = $(`input[name="${inputName}"]`).val();
        if (!val) return '';
        const date = new Date(val);
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    }

    function loadData() {
        const sort_name = $('input[name="sort_name"]').val() || 'created_at';
        const sort = $('input[name="sort"]').val() || 'DESC';
        const search = encodeURIComponent($('input[name="search"]').val() || '');
        const vd = $('select[name="vd"]').val() || '10';
        const searchdate = `${formatDate('datefilterstart')}sd${formatDate('datefilterend')}`;
        actfilterdate(url, sort_name, sort, 1, vd, search, searchdate);
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

    const formatDate = (val) => {
        const date = new Date(val);
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    };

    const getSearch = () => encodeURIComponent($('input[name="search"]').val());
    const getVd = () => $('select[name="vd"]').val();
    const searchdate = formatDate($('input[name="datefilterstart"]').val()) + 'sd' + formatDate($('input[name="datefilterend"]').val());

    $('button[btn="page_awal"]').off().on('click', function () {
        actfilterdate(url, name, sort, 1, getVd(), getSearch(), searchdate);
    });

    $('button[btn="page_min"]').off().on('click', function () {
        const page = Math.max(parseInt($('input[name="current_page"]').val()) - 1, 1);
        actfilterdate(url, name, sort, page, getVd(), getSearch(), searchdate);
    });

    $('button[btn="page_plus"]').off().on('click', function () {
        const page = parseInt($('input[name="current_page"]').val()) + 1;
        actfilterdate(url, name, sort, page, getVd(), getSearch(), searchdate);
    });

    $('button[btn="page_akhir"]').off().on('click', function () {
        const page = parseInt($('input[name="last_page"]').val());
        actfilterdate(url, name, sort, page, getVd(), getSearch(), searchdate);
    });
}

function datefilter() {
    const startVal = $('input[name="datefilterstart"]').val();
    const endVal = $('input[name="datefilterend"]').val();

    if (startVal && endVal) {
        const formatDate = (val) => {
            const date = new Date(val);
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        };

        const datefilterstart = formatDate(startVal);
        const datefilterend = formatDate(endVal);
        const searchdate = `${datefilterstart}sd${datefilterend}`;

        const url = $('input[name="url_active"]').val() || window.location.pathname;
        const vd = $('select[name="vd"]').val() || '10';
        const keysearch = $('input[name="search"]').val() || '';
        const sort_name = $('input[name="sort_name"]').val() || 'created_at';
        const sort = $('input[name="sort"]').val() || 'DESC';

        actfilterdate(url, sort_name, sort, 1, vd, encodeURIComponent(keysearch), searchdate);
    } else {
        alert('Silakan isi kedua tanggal terlebih dahulu.');
    }
}
