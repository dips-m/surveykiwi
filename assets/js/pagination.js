    $(document).ready(function() {

        var itemsPerPage = 10;


        /**
         * Generic pagination function
         */
        function setupPagination(rowSelector, paginationSelector) {

            var $rows = $(rowSelector);
            var $pagination = $(paginationSelector);

            var totalItems = $rows.length;
            var totalPages = Math.ceil(totalItems / itemsPerPage);

            if (totalPages <= 1) {
                $pagination.hide();
                $rows.show();
                return;
            }

            var currentPage = 1;


            function renderPage(page) {

                currentPage = page;

                var start = (page - 1) * itemsPerPage;
                var end = start + itemsPerPage;

                $rows.hide();

                $rows.slice(start, end).show();

                renderPagination();
            }


            function renderPagination() {

                var html = '';

                html += '<ul class="pagination pagination-sm" style="margin: 0;">';


                // Previous
                if (currentPage === 1) {

                    html += '<li class="disabled">';
                    html += '<a href="javascript:void(0);">&laquo;</a>';
                    html += '</li>';

                } else {

                    html += '<li>';
                    html += '<a href="javascript:void(0);" data-page="' + (currentPage - 1) + '">&laquo;</a>';
                    html += '</li>';

                }


                // Page numbers
                for (var i = 1; i <= totalPages; i++) {

                    if (i === currentPage) {

                        html += '<li class="active">';
                        html += '<a href="javascript:void(0);">' + i + '</a>';
                        html += '</li>';

                    } else {

                        html += '<li>';
                        html += '<a href="javascript:void(0);" data-page="' + i + '">' + i + '</a>';
                        html += '</li>';

                    }

                }


                // Next
                if (currentPage === totalPages) {

                    html += '<li class="disabled">';
                    html += '<a href="javascript:void(0);">&raquo;</a>';
                    html += '</li>';

                } else {

                    html += '<li>';
                    html += '<a href="javascript:void(0);" data-page="' + (currentPage + 1) + '">&raquo;</a>';
                    html += '</li>';

                }

                html += '</ul>';

                $pagination.html(html);
            }


            $pagination.on('click', 'a[data-page]', function(e) {

                e.preventDefault();

                var page = parseInt($(this).attr('data-page'), 10);

                if (page >= 1 && page <= totalPages) {
                    renderPage(page);
                }

            });


            renderPage(1);
        }


        // Schedule pagination
        setupPagination(
            '#schedule-list .schedule-row',
            '#schedule-pagination'
        );

    });