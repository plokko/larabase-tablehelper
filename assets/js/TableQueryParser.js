import qs from 'qs';
import { router, usePage } from '@inertiajs/vue3'

const page = usePage();

function filterKeyPrefix(data, prefix) {
    return (!prefix) ? data :
        Object.keys(data)
            .filter(key => key.startsWith(prefix))
            .reduce((obj, key) => {
                const newKey = key.substring(prefix.length);
                obj[newKey] = data[key];
                return obj;
            }, {})
}

class SortParams {
    static parse(queryString) {
        //console.log('queryString', queryString);
        return (Array.isArray(queryString) ? queryString : queryString?.split(','))
            // Remove empty
            .filter((e) => !!e)
            /// Unique
            .filter((e, i, self) => i === self.indexOf(e))
            /// Map to object
            .map(key => {
                let order = 'asc';
                if (key.startsWith('-')) {
                    key = key.substring(1);
                    order = 'desc';
                }
                return { key, order };
            }) || [];
    }

    static stringify(sorts) {
        return sorts.map((s) => `${s.order === 'desc' ? '-' : ''}${s.key}`)
    }
}

/**
 * Helper class to parse and generate query string for tables.
 * @property {array} filter - Array of filters.
 * @property {array} sort - Array of objects with key and order properties.
 * @property {int} page - Current page.
 *
 * @property {Object} table
 * @property {Array} data
 *
 */
class TableQueryParser {
    constructor(name, opt) {
        this.loading = false;
        this.name = name ?? 'default';

        /// param names
        this.dataPrefix = opt?.dataPrefix ?? '_tables';
        this.filterKey = opt?.parameters?.filter ?? 'filter';
        this.sortKey = opt?.parameters?.sort ?? 'sort';

        this._init();
    }

    get data() {
        return this.table?.data;
    }

    get headers() {
        return this.table?.headers;
    }

    get search() {
        return this.table?.search;
    }

    get searchParam() {
        return this.search?.name;
    }

    get searchValue() {
        return this.queryData[this.searchParam];
    }

    get perPage() {
        return this.table?.per_page;
    }

    get allowedPageSizes() {
        return this.table?.allowedPageSizes ?? [];
    }

    set searchValue(search) {
        ///tbd!
        if (!this.searchParam) {
            console.warn('Cannot search with an empty search param')
            return;
        }
        console.warn(`Serching by ${search}`)
        this.reloadWith({
            page: 1,
            search,
        });
    }

    get showSearch() {
        return this.table?.search && (this.table?.search.show !== false);
    }

    /// Get filter values
    get filterValues() {

        const defaults = this.headers.filter(h => h.filterable).map(h => h.name)
            .reduce((obj, key) => (obj[key] = null, obj), {});

        return Object.assign(defaults, this.filter || {});
    }

    get filters() {
        return this.headers
            .filter(h => h.filterable)
            .map(h => ({
                name: h.name,
                label: h.title,
                value: this.filter[h.name] ?? null,
            }));

    }

    set filters(filters) {
        const filter = {};
        for (const h of filters) {
            if (h.value) {
                filter[h.name] = h.value;
            }
        }

        this.reloadWith({
            filter,
            page: 1,// Reset page
        });
        //alert(JSON.stringify(filter, null, 2));
    }

    get activeFilters() {
        return this.headers.filter(h => h.filterable && this.filter[h.name])
            .map(h => ({
                name: h.name,
                label: h.title,
                value: this.filter[h.name] ?? null,
            }));

    }

    _init() {
        this.query = qs.parse(window.location.search, { ignoreQueryPrefix: true });


        this.table = page.props[this.dataPrefix][this.name];
        this.prefix = this.table?.prefix ?? '';

        this.queryData = filterKeyPrefix(this.query, this.prefix);

        //console.warn('QUERYDATA:::', this.queryData);

        this.filter = this.queryData[this.filterKey] || {}

        this.sort = SortParams.parse(this.table?.order_by ?? this.queryData[this.sortKey]);

        this.page = parseInt(this.queryData.page) || 1;
    }

    _getUrlParams(filter, sort, page, perPage, search) {
        let query = JSON.parse(JSON.stringify(this.query));///deep clone
        const prefix = this.prefix;

        /// Parsing filters
        if (filter) {
            query[`${prefix}${this.filterKey}`] = filter;
        } else {
            delete query[`${prefix}${this.filterKey}`];
        }
        /// Parse sorting
        if (sort) {
            const sorts = SortParams.stringify(sort);

            if (sorts.length > 0) {
                query[`${prefix}${this.sortKey}`] = sorts.join(',');
            } else {
                delete query[`${prefix}${this.sortKey}`];
            }
        }

        if (this.searchParam) {
            query[`${prefix}filter[${this.searchParam}]`] = search;
        }

        query[prefix + 'page'] = page || this.page;
        if (perPage)
            query[prefix + 'perPage'] = perPage;

        return qs.stringify(query);
    }

    reloadWith(params, preserveState = true) {
        const filter = params && params?.filter !== undefined ? params?.filter : this.filter;
        const sort = params && params.sort !== undefined ? params?.sort : this.sort;
        const search = params && params.search !== undefined ? params?.search : this.searchValue;
        const page = params?.page ?? this.page;
        const perPage = params?.perPage ?? params?.itemsPerPage;

        this.get(filter, sort, page, perPage, search, preserveState = true);
    }

    get(filter, sort, page, perPage, search, preserveState = true) {
        const url = location.pathname + '?' + this._getUrlParams(filter, sort, page, perPage, search);
        const resource = `${this.dataPrefix}.${this.name}`;

        console.log('tablequeryparser.get', { url, filter, sort, page, perPage, resource });

        router.visit(url, {
            only: [resource],
            preserveState,

            onStart: visit => {
                this.loading = true;
                return true;
            },
            onFinish: visit => {
                this.loading = false;
                this._init();
            },
        });
    }

    reload() {
        router.reload({
            only: [this.name],
            onStart: visit => {
                this.loading = true;
                return true;
            },
            onFinish: visit => {
                this.loading = false;
                this._init();
            },
        })
    }
}

export {
    TableQueryParser,
    SortParams,
};
