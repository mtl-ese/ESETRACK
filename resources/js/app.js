// Import jQuery and make it globally available
import $ from 'jquery';
window.$ = window.jQuery = $;

// Import Bootstrap's CSS & JavaScript
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';

// Import FontAwesome for icons
import '@fortawesome/fontawesome-free/css/all.min.css';

// Import DataTables core and Bootstrap 4 styling
import 'datatables.net-bs4/css/dataTables.bootstrap4.min.css';



// Import DataTables JS functionality
import 'datatables.net';
import 'datatables.net-responsive';
import 'datatables.net-buttons';

// Import DataTables dependencies for exporting features
import 'jszip';
import 'pdfmake';
import 'pdfmake/build/vfs_fonts';
import 'datatables.net-buttons/js/buttons.html5.min.js';
import 'datatables.net-buttons/js/buttons.print.min.js';
import 'datatables.net-buttons/js/buttons.colVis.min.js';

// Initialize DataTable after DOM loads
$(document).ready(function () {
    $('#example1').each(function () {
        const tableTitle = $(this).data('title') || 'Data Export';
        const currentPath = window.location.pathname;
        const materialsPaths = ['/store', '/purchase', '/returns', '/emergency', '/recovery', '/acquired', '/emergency/return'];
        const shouldShowMaterialsButton = materialsPaths.some(path => currentPath === path);

        const buttons = [
            {
                extend: 'collection',
                text: '<i class="fas fa-download p-2"></i> Export',
                className: 'btn btn-warning btn-sm',
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy p-2"></i> Copy',
                        className: 'dropdown-item',
                        title: tableTitle,
                        exportOptions: {
                            columns: function (idx, data, node) {
                                return !$(node).hasClass('no-export');
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv p-2"></i> CSV',
                        className: 'dropdown-item',
                        title: tableTitle,
                        exportOptions: {
                            columns: function (idx, data, node) {
                                return !$(node).hasClass('no-export');
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel p-2"></i> Excel',
                        className: 'dropdown-item',
                        title: tableTitle,
                        exportOptions: {
                            columns: function (idx, data, node) {
                                return !$(node).hasClass('no-export');
                            }
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf p-2"></i> PDF',
                        className: 'dropdown-item',
                        action: function (e, dt, node, config) {
                            const { jsPDF } = window.jspdf;
                            const doc = new jsPDF();

                            // Get the table element and its title
                            const table = dt.table().node();
                            const tableTitle = table.getAttribute('data-title') || 'Document'; // Fallback to 'Document' if no title

                            // Hide columns with 'no-export' class
                            const noExportColumns = table.querySelectorAll('th.no-export, td.no-export');
                            const originalDisplays = [];
                            noExportColumns.forEach(col => {
                                originalDisplays.push(col.style.display);
                                col.style.display = 'none';
                            });

                            // Generate PDF with title
                            doc.autoTable({
                                html: table,
                                styles: {
                                    fontSize: 8,
                                    cellPadding: 5,
                                    overflow: 'linebreak',
                                    lineColor: [0, 0, 0],
                                    lineWidth: 0.1,
                                    valign: 'center',
                                    halign: 'center',
                                    fillColor: [255, 255, 255],
                                    textColor: [0, 0, 0],
                                    cellPadding: { top: 3, right: 3, bottom: 3, left: 3 }
                                },
                                headStyles: {
                                    fillColor: '#001f3f',
                                    textColor: '#ffffff',
                                    fontStyle: 'bold',
                                    fontSize: 10,
                                    halign: 'center'
                                },
                                margin: {
                                    top: 30,  // Extra space for title
                                    left: 10,
                                    right: 10,
                                    bottom: 20
                                },
                                didParseCell: function (data) {
                                    if (data.cell.raw.classList.contains('no-export')) {
                                        data.cell.text = '';
                                    }
                                },
                                didDrawPage: function (data) {
                                    // Add title on first page
                                    if (data.pageNumber === 1) {
                                        doc.setFontSize(16);
                                        doc.text(
                                            tableTitle,
                                            doc.internal.pageSize.width / 2,
                                            15,
                                            { align: 'center' }
                                        );
                                    }

                                    // Add page numbers
                                    const pageCount = doc.internal.getNumberOfPages();
                                    doc.setFontSize(10);
                                    doc.text(
                                        `Page ${data.pageNumber} of ${pageCount}`,
                                        doc.internal.pageSize.width / 2,
                                        doc.internal.pageSize.height - 10,
                                        { align: 'center' }
                                    );
                                }
                            });

                            // Restore hidden columns
                            noExportColumns.forEach((col, index) => {
                                col.style.display = originalDisplays[index];
                            });

                            // Create filename from title (sanitized) with current date
                            const sanitizedTitle = tableTitle
                                .replace(/[^a-zA-Z0-9\s]/g, '')
                                .trim()
                                .replace(/\s+/g, '_');
                            const formattedDate = new Date().toISOString().slice(0, 10);

                            // Open in new window with proper filename
                            const pdfUrl = doc.output('bloburl');
                            const link = document.createElement('a');
                            link.href = pdfUrl;
                            link.download = `${sanitizedTitle}_${formattedDate}.pdf`;
                            link.target = '_blank';
                            link.click();
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print p-2"></i> Print',
                        className: 'dropdown-item',
                        title: tableTitle,
                        exportOptions: {
                            columns: function (idx, data, node) {
                                return !$(node).hasClass('no-export');
                            }
                        }
                    }
                ]
            },
            {
                extend: 'colvis',
                text: '<i class="fas fa-columns p-2"></i> Column Visibility',
                className: 'btn btn-warning btn-sm ms-4'
            }
        ];

        if (shouldShowMaterialsButton) {
            buttons.push({
                text: '<i class="fas fa-boxes p-2"></i> View Materials',
                className: 'btn btn-warning btn-sm ms-4',
                action: function () {
                    const redirectMap = {
                        '/store': '/store/materials',
                        '/purchase': '/purchase/materials',
                        '/emergency': '/emergency/materials',
                        '/recovery': '/recovery/materials',
                        '/acquired': '/acquired/materials',
                        '/returns': '/returns/materials',
                        '/emergency/return': '/emergency/return/materials'
                    };
                    const path = window.location.pathname;
                    // Prefer more specific routes first (longer keys), so '/emergency/return' wins over '/emergency'
                    const keys = Object.keys(redirectMap).sort((a, b) => b.length - a.length);
                    for (const key of keys) {
                        if (path === key || path.startsWith(`${key}/`)) {
                            window.location.href = redirectMap[key];
                            return;
                        }
                    }
                    window.location.href = '/store/materials';
                }
            });
        }

        const table = $(this).DataTable({
            responsive: false,
            lengthChange: true,
            processing: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            language: {
                search: "<span class='search-icon'>🔍</span> <span class='search-label'>Search:</span>",
                searchPlaceholder: "Type to filter...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                zeroRecords: "No matching records found",
                emptyTable: "No data available in table",
                paginate: {
                    first: "<i class='fas fa-angle-double-left'></i>",
                    previous: "<i class='fas fa-angle-left'></i> Prev",
                    next: "Next <i class='fas fa-angle-right'></i>",
                    last: "<i class='fas fa-angle-double-right'></i>"
                },
                aria: {
                    sortAscending: ": activate to sort column ascending",
                    sortDescending: ": activate to sort column descending"
                }
            },
            dom: '<"row mb-3"<"col-md-6"B><"col-md-6"f>>rtip',
            buttons: buttons
        });

        // Append buttons above the table dynamically
        table.buttons().container().prependTo($(this).closest('#example1-wrapper'));
    });
});



