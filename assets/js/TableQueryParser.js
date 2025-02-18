import qs from 'qs';
import {router} from '@inertiajs/vue3'

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
        console.log('queryString', queryString);
        return queryString?.split(',')
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
 */
class TableQueryParser {
    constructor(name, prefix, opt) {
        this.loading = false;

        this.query = qs.parse(window.location.search, {ignoreQueryPrefix: true});

        this.name = name;
        this.prefix = prefix;

        const filterKey = opt?.parameters?.filter || 'filter';
        this.filterKey = filterKey;
        const sortKey = opt?.parameters?.sort || 'sort';
        this.sortKey = sortKey;


        const queryData = filterKeyPrefix(this.query, prefix);

        this.queryData = queryData;

        this.filter = queryData[filterKey] || {}
        this.sort = SortParams.parse(queryData[sortKey]);
        this.page = queryData.page || 1;

    }


    getUrlParams(filter, sort, page) {

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

        this.query[prefix + 'page'] = page || this.page;

        return qs.stringify(query);
    }


    get(filter, sort, page) {
        const url = location.pathname + '?' + this.getUrlParams(filter, sort, page);

        console.log(url);

        router.visit(url, {
            only: [this.name],

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
};
