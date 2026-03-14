import { JSDOM } from 'jsdom';

let document = null;

export function stripHtml(html: string) {
    document ??= new JSDOM().window.document;

    const el = document.createElement('div');

    el.innerHTML = html;

    return el.textContent;
}
