import qs from 'qs';
import {router, usePage} from '@inertiajs/vue3'

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
                return {key, order};
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

        this.query = qs.parse(window.location.search, {ignoreQueryPrefix: true});

        this.name = name ?? 'default';

        /// param names
        this.dataPrefix = opt?.dataPrefix ?? '_tables';
        this.filterKey = opt?.parameters?.filter ?? 'filter';
        this.sortKey = opt?.parameters?.sort ?? 'sort';
        const page = usePage();

        this.table = page.props[this.dataPrefix][this.name];
        this.prefix = this.table?.prefix ?? '';

        this.queryData = filterKeyPrefix(this.query, this.prefix);

        console.warn('QUERYDATA:::', this.queryData);

        this.filter = this.queryData[this.filterKey] || {}

        this.sort = SortParams.parse(this.table?.order_by ?? this.queryData[this.sortKey]);

        this.page = parseInt(this.queryData.page) || 1;
    }

    get data() {
        return this.table?.data;
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

    set searchValue(search) {
        ///tbd!
        if (!this.searchParam) {
            console.warn('Cannot search with an empty search param')
            return;
        }
        this.reloadWith({
            page: 1,
            search,
        });
    }

    get showSearch() {
        return this.table?.search && (this.table?.search.show !== false);
    }

    _getUrlParams(filter, sort, page, search) {
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
            query[`${prefix}${this.searchParam}`] = search;
        }

        query[prefix + 'page'] = page || this.page;

        return qs.stringify(query);
    }

    reloadWith(params) {

        const filter = params && params?.filter !== undefined ? params?.filter : this.filter;
        const sort = params && params.sort !== undefined ? params?.sort : this.sort;
        const search = params && params.search !== undefined ? params?.search : this.searchValue;
        const page = params?.page ?? this.page;

        this.get(filter, sort, page, search);
    }

    get(filter, sort, page, search) {
        const url = location.pathname + '?' + this._getUrlParams(filter, sort, page, search);
        const resource = `${this.dataPrefix}.${this.name}`;
        //console.log('tablequeryparser.get', { url,filter, sort, page,  resource });

        router.visit(url, {
            only: [resource],

            onStart: visit => {
                this.loading = true;
            },
            onFinish: visit => {
                this.loading = false;
            },
        })
    }

    reload() {
        router.reload({
            only: [this.name],
            onStart: visit => {
                this.loading = true;
            },
            onFinish: visit => {
                this.loading = false;
            },
        })
    }
}

export {
    TableQueryParser,
    SortParams,
};
