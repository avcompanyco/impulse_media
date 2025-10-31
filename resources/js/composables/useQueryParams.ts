import { onMounted, ref } from 'vue';


export function useQueryParams(url: string) {
    // /path?param1=value1&param2=value2
    if (!url) {
        return {};
    }
    const url_array = url.split('?');
    if (url_array.length === 1) {
        return {};
    }
    const url_query = url_array[1];
    const queryParams = url_query.split('&');
    const queryParamsObject = queryParams.map(param => {
        const [key, value] = param.split('=');
        return [key, value];
    });
    return Object.fromEntries(queryParamsObject);
}
