'use strict';

/*  When the class is wrapped in this, not sure how to pass everything.
(function(window, $, Routing, swal) {
    class SurvosDataTable {
})(window, jQuery, Routing, swal);
*/

var commonButtons = {
    'json': {
        text: '<i class="fas fa-share"></i>',
        action: function (e, dt, node, config) {
            $(this).target = "_blank";
            let dtUrl = dt.ajax.url();
            var newWindow = window.open(dtUrl);
            newWindow.focus()
            return false;

            // var tableData = tables.table( $(this).parents('table') );
            // there's got to be a way to get $this datatable!
            // let url = $this.table().ajax.url();
            // let url = $sitesTable.ajax.url();
            // window.open(url);
            // return false;
        }
    },
    'selectVisible': {
        text: "<i class='fas fa-check'></i>Visible",
        key: {
            key: 'v',
            altKey: true
        }, action: function (e, dt, button, config) {
            dt.rows({page: 'current'}).select();
        }
    },
    'refresh': {
        text: '<i title="Refresh" class="fas fa-sync"></i>',
        key: {
            key: 'r',
            altKey: true
        },
        action: function (e, dt, button, config) {
            let tableId = dt.table().node().getAttribute('id');
            let t = dt.table();
            console.log(tableId, e, dt);
            console.log(t.ajax.ur);
            dt.clear().draw();
            t.ajax.reload();
        }
    },
};

let buttons =    [
    // 'selectAll',
    // 'selectNone',
];

export default class SurvosDataTable {
        constructor($el, columns, options) {
            if (!options) {
                options = {};
            }
            this.el = $el;
            this.columns = columns;
            this.url = options.url || $el.data('dtAjax');
            this.buttons = options.buttons ? options.buttons : [
                commonButtons['json'],
                commonButtons['refresh'],
                commonButtons['selectVisible']
            ];
            // @todo: add custom buttons

            console.log("Setting up " + $el.attr('id') + ' with ' + this.url);

            this.debug = false;

        }


    initFooter() {
        var footer = this.el.find('tfoot');
        if (footer.length > 0) {
            return; // do not initiate twice
        }

        var handleSelect = function (column) {
            var select = $('<select class="form-control"></select>');
            var createOptions = function(items) {
                select.empty();
                select.append('<option value="">Select option</option>');
                _.each(items, function (label, value) {
                    select.append('<option value="'+value+'">'+label+'</option>');
                });
            };

            if (column.filter.choices) {
                createOptions(column.filter.choices);
            } else if (column.filter.choices_url) {
                survosApp.apiRequest({url: column.filter.choices_url}).then(createOptions);
            }

            return select;
        };
        var handleInput = function (column) {
            var input = $('<input class="form-control" type="text">');
            input.attr('placeholder', column.filter.placeholder || column.data );
            return input;
        };

        this.debug && console.log('adding footer');
        var tr = $('<tr>');
        var that = this;
        console.log(this.columns);
        this.columns.forEach(function (column, index) {
            console.log(column, index);
            var td = $('<td>');
            if (column.filter !== undefined) {
                var el;
                if (column.filter === true || column.filter.type === 'input') {
                    el = handleInput(column);
                } else if (column.filter.type === 'select') {
                    el = handleSelect(column);
                }
                that.handleFieldSearch(that.el, el, index);

                td.append(el);
            }
            tr.append(td);
        });
        footer = $('<tfoot>');
        footer.append(tr);
        console.log(footer);
        this.el.append(footer);

        // see http://live.datatables.net/giharaka/1/edit for moving the footer to below the header
    }

    removeTableSelection($tableEl) {
        this.debug && console.log('removeTableSelection');
        var rows = $tableEl.DataTable().rows();
        if (rows.deselect) {
            rows.deselect();
        }
    }

    handleFieldSearch($table, $field, columnIndex) {
        var that = this;
        function getValue($el) {
            if ($el.is(':checkbox')) {
                return $el.filter(':checked').val();
            } else if ($el.is('select')) {
                return $el.val();
            } else {
                return $el.val();
            }
        }
        var filter = function () {
            that.removeTableSelection($table);
            var value = getValue($field);
            console.log(value);
            $table.dataTable().api().column(columnIndex).search(value).draw();
        };
        $field.on('change', filter);
        if (getValue($field)) {
            filter();
        }
    }

    initDataTableWidgets(container, $table) {
        var info = $table.dataTable().api().page.info();
        var card = $table.closest('.card');
        card.find('[data-totals="rows"]').html(info.recordsDisplay);
        card.find('[data-totals="progress"]').html(Math.round((info.start / info.recordsDisplay) * 100));
        this.debug && console.log('page info', info);
    }

    getAccessToken() {
            return $('body').data('accessToken');
        }

        dataTableParamsToApiPlatformParams(params) {
            var apiData = {
                page: 1
            };

            if (params.length) {
                apiData.itemsPerPage = params.length;
            }

            // this is the global search, should really be elasticsearch!  Or it could be the primary text field, like title, defined in the table, search-field
            if (params.search && params.search.value) {
                apiData.description = params.search.value;
                console.error(params, apiData);
            }

            params.columns.forEach(function(column, index) {
                console.log(column);
                if (column.search && column.search.value) {
                    let value = column.search.value;
                    // check the first character for a range filter operator

                    // data is the column field, at least for right now.
                    apiData[column.data] = value;
                }
            });

            if (params.start) {
                // was apiData.page = Math.floor(params.start / params.length) + 1;
                apiData.page = Math.floor(params.start / apiData.itemsPerPage) + 1;
            }

            return apiData;
        }

        apiRequest(options, apiData) {

            if (typeof options === 'string') {
                options = {
                    url: options
                };
            }

            // this was _.defaults(), changed to remove the _ dependency, BUT this may be problematic!!
            //  must be a better way!  https://www.sitepoint.com/es6-default-parameters/
            $.extend(options, {
                headers: {},
                dataType: 'json',
            });
            $.extend(options.headers, {
                Authorization: 'Bearer ' + this.getAccessToken(),
                Accept: 'application/ld+json'
            });

            /*
            console.log('------',apiData.itemsPerPage, params.length);
            if (params.start) {
                apiData.page = Math.floor(params.start / params.length) + 1;
            }
            */

            let that = this;
            return function (params, callback, settings) {
                var out = [];

                // this is the data sent to API platform!
                options.data = that.dataTableParamsToApiPlatformParams(params);
                this.debug && console.log(params, options.data);
                console.log(`DataTables is requesting ${params.length} starting at ${params.start}`);

                console.log(options.url, JSON.stringify(options.data));


                var jqxhr = $.ajax(options)
                    .fail(function (jqXHR, textStatus, errorThrown) {
                        console.error(textStatus, errorThrown);
                    })
                    .done(function (hydraData, textStatus, jqXHR) {

                        // get the next page from hydra
                        let next = hydraData["hydra:view"]['hydra:next'];
                        var total = hydraData['hydra:totalItems'];
                        var itemsReturned = hydraData['hydra:member'].length;
                        let apiOptions = options.data;

                        if (params.search) {
                            console.log(`dt search: ${params.search.value}`);
                        }
                        console.log(`dt request: ${params.length} starting at ${params.start}`);

                        let first = (apiOptions.page-1) * apiOptions.itemsPerPage;
                        let d = hydraData['hydra:member'];
                        this.debug && console.log(d.map( obj => obj.id ));

                        // this one could be a partial, just json, etc.  Also we don't need it if it's on a page boundary
                        if (next) {
                            $.ajax({
                                url: next,
                                Accept: 'application/ld+json'
                            }).done(function(json)
                            {
                                d = d.concat(json['hydra:member']);
                                this.debug && console.log(d.map( obj => obj.id ));
                                if (this.debug && console && console.log) {
                                    console.log(`  ${itemsReturned} (of ${total}) returned, page ${apiOptions.page}, ${apiOptions.itemsPerPage}/page first: ${first} :`, d);
                                }
                                d = d.slice(params.start - first, (params.start - first) + params.length);

                                itemsReturned = d.length;
                                console.log(`2-page callback with ${total} records`);
                                callback({
                                    draw: params.draw,
                                    data: d,
                                    recordsTotal: total,
                                    recordsFiltered: itemsReturned,
                                });

                                // console.log(params, hydraData, total);
                                // could check hydra:view to see if it's partial
                            });
                        } else {
                            console.log(`Only-page callback with ${itemsReturned} records`);
                            callback({
                                draw: params.draw,
                                data: d,
                                recordsTotal: total,
                                recordsFiltered: itemsReturned,
                            });

                        }

                        // likely need caching, since in most cases we'll need two requests


                    });

                /*
                    jqxhr.on('xhr.dt', function ( e, settings, json, xhr ) {
                        console.log(e, settings, json);
                        // Note no return - manipulate the data directly in the JSON object.
                    } );
                */

                // return jqxhr;
            }
        }


        render() {
            // var url = this.el.data('ajax'); // or options?
            $.ajaxSetup({ cache: true});

            console.log(this.buttons);

            this.el.DataTable({
                ajax: this.apiRequest({
                    url: this.url
                }),
                orderCellsTop: true,
                id: '@id',
                columns: this.columns,
                serverSide: true,
                processing: true,
                scrollY: '50vh', // vh is percentage of viewport height, https://css-tricks.com/fun-viewport-units/
                // scrollY: true,
                deferRender: true,
                // displayLength: 17, // not sure how to adjust the 'length' sent to the server
                // pageLength: 14,
                dom: 'iBft',
                buttons: this.buttons,
                scroller: {
                    // rowHeight: 20,
                    displayBuffer: 3,
                    loadingIndicator: true,
                }
            });
            this.debug && console.warn(this.el.attr('id') + ' rendered!');
            this.debug && console.log(this.url); // , this.el.data());
        }

}